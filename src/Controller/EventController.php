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

    private function getLoggedUser(): ?User
    {
        return $this->getUser();
    }

    private function denyUnlessLogged()
    {
        if (!$this->getLoggedUser()) {
            throw new AccessDeniedException();
        }
    }

    #[Route('/evenement', name: 'app_event')]
    public function index(EventRepository $eventRepository, Security $security, DateFormatterService $dateFormatter): Response
    {
        setlocale(LC_TIME, 'fr_FR.UTF-8');

        $user = $security->getUser();

        if ($user) {
            $events = $eventRepository->findEventsWithoutUnconfirmedRegistration($user);
        } else {
            $events = $eventRepository->findLimitedEvents();
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
            $comment->setCreatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

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
        $this->denyUnlessLogged();

        if ($this->getLoggedUser() !== $event->getOrganisator()) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à inviter des utilisateurs à cet événement.');
        }

        $form = $this->createForm(InviteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedUsers = $form->get('users')->getData();

            foreach ($selectedUsers as $user) {
                $existingRegistration = $registrationRepository->findOneBy([
                    'user' => $user,
                    'event' => $event,
                ]);

                if ($existingRegistration) {
                    continue;
                }

                $registration = new Registration();
                $registration->setEvent($event);
                $registration->setUser($user);
                $registration->setRegistrationDate(new \DateTime('now'));
                $registration->setIsInvited(true);
                $registration->setHasConfirmed(false);
                $registration->setHasParticipated(false);

                $entityManager->persist($registration);

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
        $this->denyUnlessLogged();
        $user = $this->getLoggedUser();

        if (null === $user) {
            return $this->redirectToRoute('app_login');
        }

        $existingRegistration = $registrationRepository->findOneBy(['event' => $event, 'user' => $user]);

        if ($existingRegistration) {
            if (!$existingRegistration->isHasConfirmed()) {
                $existingRegistration->setHasConfirmed(true);
                $em->flush();
            } else {
            }
        } else {
            $registration = new Registration();
            $registration->setEvent($event);
            $registration->setUser($user);
            $registration->setHasConfirmed(true);
            $registration->setRegistrationDate(new \DateTime());

            $em->persist($registration);
            $em->flush();
        }

        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }

    #[Route('/evenement/{id}/cancel-participation', name: 'app_event_cancel_participation')]
    public function cancelParticipation(Event $event, RegistrationRepository $registrationRepository, EntityManagerInterface $em, Security $security): Response
    {
        $this->denyUnlessLogged();
        $user = $this->getLoggedUser();

        $existingRegistration = $registrationRepository->findOneBy(['event' => $event, 'user' => $user]);

        if ($existingRegistration && $existingRegistration->isHasConfirmed()) {
            $existingRegistration->setHasConfirmed(false);
            $em->flush();
        }

        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }

    #[Route('/mon-compte/evenements', name: 'app_account_myevents')]
    public function indexAccountEvent(Security $security, UserRepository $userRepository, EventRepository $eventRepository): Response
    {
        $this->denyUnlessLogged();
        $user = $this->getLoggedUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // $userRepo = $userRepository->find($this->getUser());
        $events = $eventRepository->findEventsByUser($user);

        return $this->render('account_event/index.html.twig', [
            'user' => $user,
            'events' => $events,
        ]);
    }

    #[Route('/mon-compte/evenements/new', name: 'app_account_event_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $event = new Event();

        $this->denyUnlessLogged();
        $user = $this->getLoggedUser();

        $event->setOrganisator($user);

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

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
        $this->denyUnlessLogged();
        $user = $this->getLoggedUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $event = $em->getRepository(Event::class)->find($id);

        if (!$event) {
            throw $this->createNotFoundException('L\'événement demandé n\'existe pas.');
        }

        $isOrganisator = $event->getOrganisator() === $user;
        $isAdmin = $security->isGranted('ROLE_ADMIN');

        if (!$isOrganisator && !$isAdmin) {
            return $this->redirectToRoute('app_account_myevents');
        }

        $registrations = $em->getRepository(Registration::class)->findBy(['event' => $event]);
        foreach ($registrations as $registration) {
            $em->remove($registration);
        }

        $em->remove($event);
        $em->flush();

        return $this->redirectToRoute('app_home');
    }

    #[Route('/mon-compte/evenements/{id}/editer', name: 'app_myevent_edit', methods: ['GET', 'POST'])]
    public function editEvent(int $id, Security $security, EntityManagerInterface $em, Request $request): Response
    {
        $this->denyUnlessLogged();
        $user = $this->getLoggedUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $event = $em->getRepository(Event::class)->find($id);

        if (!$event) {
            throw $this->createNotFoundException('L\'événement demandé n\'existe pas.');
        }

        $isOrganisator = $event->getOrganisator() === $user;
        $isAdmin = $security->isGranted('ROLE_ADMIN');

        if (!$isOrganisator && !$isAdmin) {
            return $this->redirectToRoute('app_account_myevents');
        }


        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_account_myevents');
        }

        return $this->render('account_event/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
