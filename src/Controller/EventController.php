<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Event;
use App\Entity\Registration;
use App\Entity\Notification;
use App\Entity\Comment; 
use App\Entity\User; 
use App\Form\InviteType;
use App\Form\CommentType; 
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use App\Form\EventType;
use App\Repository\CommentRepository;
use App\Repository\RegistrationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Service\DateFormatterService;

class EventController extends AbstractController
{
    private $entityManager;
    private $userRepository;
    private $commentRepository; 

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, CommentRepository $commentRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->commentRepository = $commentRepository; 
    }

    #[Route('/evenement', name: 'app_event')]
    public function index(EventRepository $eventRepository, Security $security, DateFormatterService $dateFormatter): Response
    {
        setlocale(LC_TIME, 'fr_FR.UTF-8');

        $user = $security->getUser();
        $limit = null;  

        if ($user) {
            $events = $eventRepository->findEventsWithoutUnconfirmedRegistration($user, $limit);  
        } else {
            $events = $eventRepository->findLimitedEvents($limit);  
        }

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'user' => $user,
            'dateFormatter' => $dateFormatter,
        ]);
    }

    #[Route('/evenement/{id}', name: 'app_event_show')]
    public function show(Event $event, RegistrationRepository $registrationRepository, Security $security, Request $request): Response
    {
        $user = $security->getUser();
        $existingRegistration = $registrationRepository->findOneBy(['event' => $event, 'user' => $user]);
        $confirmedParticipants = $registrationRepository->findBy(['event' => $event, 'hasConfirmed' => true]);
    
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);
    
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setEvent($event);
            $comment->setUser($user);
            $comment->setCreatedAt(new \DateTimeImmutable()); // Setting createdAt here
    
            $this->entityManager->persist($comment);
            $this->entityManager->flush();
    
            $this->addFlash('success', 'Votre commentaire a été publié avec succès !');
            
            // Redirigez à nouveau vers la page de l'événement pour afficher le nouveau commentaire
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }
    
        return $this->render('event/show.html.twig', [
            'event' => $event,
            'existingRegistration' => $existingRegistration,
            'participants' => $confirmedParticipants,
            'comment_form' => $commentForm->createView(),
        ]);
    }

    #[Route('/evenement/{id}/invite', name: 'app_event_invite')]
    public function invite(Request $request, Event $event, RegistrationRepository $registrationRepository, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() !== $event->getOrganisator()) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à inviter des utilisateurs à cet événement.');
        }

        $form = $this->createForm(InviteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedUsers = $form->get('users')->getData(); // Récupérez les utilisateurs sélectionnés.

            foreach ($selectedUsers as $user) {
                $existingRegistration = $registrationRepository->findOneBy([
                    'user' => $user,
                    'event' => $event,
                ]);

                if ($existingRegistration) {
                    // Cet utilisateur est déjà enregistré pour cet événement.
                    $this->addFlash('warning', 'L\'utilisateur ' . $user->getEmail() . ' est déjà invité à cet événement.');
                    continue; // Passez à l'utilisateur suivant sans créer de nouvelle invitation.
                }

                $registration = new Registration();
                $registration->setEvent($event);
                $registration->setUser($user);
                $registration->setRegistrationDate(new \DateTime('now'));
                $registration->setIsInvited(true);
                $registration->setHasConfirmed(false);
                $registration->setHasParticipated(false);

                $entityManager->persist($registration);

                // Créez une notification pour l'utilisateur
                $notification = new Notification();
                $notification->setUser($user);

                $eventLink = $this->generateUrl('app_event_show', ['id' => $event->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
                $notificationMessage = sprintf(
                    'Vous avez été invité à l\'événement <a href="%s">%s</a>.',
                    $eventLink,
                    htmlspecialchars($event->getTitle(), ENT_QUOTES, 'UTF-8')
                );

                $notification->setMessage($notificationMessage);

                $entityManager->persist($notification);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Invitations envoyées avec succès!');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        return $this->render('event/invite.html.twig', [
            'event' => $event,
            'inviteForm' => $form->createView(),
        ]);
    }

    #[Route('/evenement/{id}/participate', name: 'app_event_participate')]
    public function participate(Event $event, RegistrationRepository $registrationRepository, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();

        // Si l'utilisateur n'est pas connecté, le rediriger vers la page de connexion.
        if (null === $user) {
            $this->addFlash('warning', 'Vous devez être connecté pour participer à un événement.');
            return $this->redirectToRoute('app_login'); // Remplacez 'app_login' par le nom de votre route de connexion.
        }

        // Vérifiez si l'utilisateur a déjà une registration pour cet événement.
        $existingRegistration = $registrationRepository->findOneBy(['event' => $event, 'user' => $user]);

        if ($existingRegistration) {
            // Si l'utilisateur a déjà une registration mais n'a pas encore confirmé sa participation, confirmez-la.
            if (!$existingRegistration->isHasConfirmed()) {
                $existingRegistration->setHasConfirmed(true);
                $em->flush();
                $this->addFlash('success', 'Votre participation a été confirmée.');
            } else {
                $this->addFlash('warning', 'Vous êtes déjà inscrit à cet événement.');
            }
        } else {
            // Si l'utilisateur n'a pas de registration, créez-en une nouvelle.
            $registration = new Registration();
            $registration->setEvent($event);
            $registration->setUser($user);
            $registration->setHasConfirmed(true);
            $registration->setRegistrationDate(new \DateTime());

            $em->persist($registration);
            $em->flush();

            $this->addFlash('success', 'Votre participation a été enregistrée.');
        }

        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }

    #[Route('/evenement/{id}/cancel-participation', name: 'app_event_cancel_participation')]
    public function cancelParticipation(Event $event, RegistrationRepository $registrationRepository, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();

        $existingRegistration = $registrationRepository->findOneBy(['event' => $event, 'user' => $user]);

        // Vérifiez si l'utilisateur a déjà confirmé sa participation.
        if ($existingRegistration && $existingRegistration->isHasConfirmed()) {
            $existingRegistration->setHasConfirmed(false);
            $em->flush();
            $this->addFlash('success', 'Votre participation a été annulée.');
        } else {
            $this->addFlash('warning', 'Vous n’êtes pas inscrit à cet événement ou avez déjà annulé.');
        }

        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }

    #[Route('/mon-compte/evenements', name: 'app_account_myevents')]
    public function indexAccountEvent(Security $security, UserRepository $userRepository): Response
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $userRepo = $userRepository->find($this->getUser());
        $events = $userRepo->getEvents();

        return $this->render('account_event/index.html.twig', [
            'user' => $user,
            'events' => $events,
        ]);
    }

    #[Route('/mon-compte/evenements/new', name: 'app_account_event_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $event = new Event();
        $user = $security->getUser();
        $event->setOrganisator($user);

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Convert the startDate string to DateTime object
            $startDateString = $form->get('startDate')->getData();
            $startDate = \DateTime::createFromFormat('Y-m-d H:i', $startDateString);
            $event->setStartDate($startDate);

            $endDateString = $form->get('endDate')->getData();
            $endDate = \DateTime::createFromFormat('Y-m-d H:i', $endDateString);
            $event->setEndDate($endDate);

            if ($event->isIsFinancialParticipation()) {
                $event->setTotalAmountCollected(0.00);
            }

            $entityManager->persist($event);

            $registration = new Registration();
            $registration->setEvent($event);
            $registration->setUser($user);
            $registration->setHasConfirmed(true);
            $registration->setRegistrationDate(new \DateTime());
            $entityManager->persist($registration);

            $entityManager->flush();

            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        return $this->render('account_event/new.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/mon-compte/evenements/{id}/supprimer', name: 'app_myevent_delete')]
    public function deleteEvent(int $id, Security $security, EntityManagerInterface $em): Response
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $event = $em->getRepository(Event::class)->find($id);

        if (!$event) {
            throw $this->createNotFoundException('L\'événement demandé n\'existe pas.');
        }

        if ($event->getOrganisator() !== $user) {
            $this->addFlash('error', 'Seul l\'organisateur de l\'événement peut le supprimer.');
            return $this->redirectToRoute('app_account_myevents');
        }

        // Suppression des inscriptions associées à l'événement
        $registrations = $em->getRepository(Registration::class)->findBy(['event' => $event]);
        foreach ($registrations as $registration) {
            $em->remove($registration);
        }

        // Suppression de l'événement
        $em->remove($event);
        $em->flush();

        $this->addFlash('success', 'Événement supprimé avec succès.');

        return $this->redirectToRoute('app_home');
    }

    #[Route('/mon-compte/evenements/{id}/editer', name: 'app_myevent_edit', methods: ['GET', 'POST'])]
    public function editEvent(int $id, Security $security, EntityManagerInterface $em, Request $request): Response
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $event = $em->getRepository(Event::class)->find($id);

        if (!$event) {
            throw $this->createNotFoundException('L\'événement demandé n\'existe pas.');
        }

        if ($event->getOrganisator() !== $user) {
            $this->addFlash('error', 'Seul l\'organisateur de l\'événement peut le modifier.');
            return $this->redirectToRoute('app_account_myevents');
        }

        // Supposons que vous ayez un EventFormType pour traiter le formulaire
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Événement modifié avec succès.');
            return $this->redirectToRoute('app_account_myevents');
        }

        return $this->render('account_event/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
