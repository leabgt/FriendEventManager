<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Event;
use App\Entity\Registration;
use App\Entity\Notification;
use App\Form\InviteType;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use App\Form\EventType;
use App\Repository\RegistrationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EventController extends AbstractController
{
    private $entityManager;
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    #[Route('/evenement', name: 'app_event')]
    public function index(): Response
    {
        return $this->render('event/index.html.twig', [
            'controller_name' => 'EventController',
        ]);
    }

    #[Route('/evenement/{id}', name: 'app_event_show')]
    public function show(Event $event, RegistrationRepository $registrationRepository, Security $security): Response
    {
        $user = $security->getUser();

        // $fundraising = $fundraisingRepository->findOneBy(['event' => $event]);
        $existingRegistration = $registrationRepository->findOneBy(['event' => $event, 'user' => $user]);

        return $this->render('event/show.html.twig', [
            'event' => $event,
            // 'fundraising' => $fundraising,  // Passez l'objet Fundraising entier
            'existingRegistration' => $existingRegistration,
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
                $notification->setMessage('Vous avez été invité à l\'événement ' . $event->getTitle() . '.');

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
    public function new(Request $request, EventRepository $eventRepository, EntityManagerInterface $entityManager, Security $security): Response
    {
        $event = new Event();

        // Récupérez l'utilisateur actuellement connecté
        $user = $security->getUser();

        // Remplissez automatiquement l'ID de l'organisateur avec l'ID de l'utilisateur
        $event->setOrganisator($user);

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrez d'abord l'événement
            $eventRepository->save($event, true);

            // Si IsParticipalFinancial est vrai, créez une cagnotte
            if ($event->isIsFinancialParticipation()) {
                $event->setTotalAmountCollected(0.00);
            }

            $registration = new Registration();
            $registration->setEvent($event);
            $registration->setUser($user);
            $registration->setHasConfirmed(true); // Car l'organisateur est automatiquement confirmé
            $registration->setRegistrationDate(new \DateTime());
            $entityManager->persist($registration);

            // Flush the entity manager to save all changes
            $entityManager->flush();

            // Obtenez l'ID de l'événement nouvellement créé
            $eventId = $event->getId();

            // Redirigez l'utilisateur vers la route app_event_show avec l'ID en tant que paramètre
            return $this->redirectToRoute('app_event_show', ['id' => $eventId]);
        }

        return $this->render('account_event/new.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }
}
