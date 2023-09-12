<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Event;
use App\Entity\Registration;
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
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/evenement/{id}/invite', name: 'app_event_invite')]
    public function invite(Request $request, Event $event, RegistrationRepository $registrationRepository): Response
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

                $this->entityManager->persist($registration);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Invitations envoyées avec succès!');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }


        return $this->render('event/invite.html.twig', [
            'event' => $event,
            'inviteForm' => $form->createView(),
        ]);
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
    public function new(Request $request, EventRepository $eventRepository, Security $security): Response
    {
        $event = new Event();

        // Récupérez l'utilisateur actuellement connecté
        $user = $security->getUser();

        // Remplissez automatiquement l'ID de l'organisateur avec l'ID de l'utilisateur
        $event->setOrganisator($user);

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventRepository->save($event, true);

            // Obtenez l'ID de l'événement nouvellement créé
            $eventId = $event->getId();

            // Redirigez l'utilisateur vers la route app_event_show avec l'ID en tant que paramètre
            return $this->redirectToRoute('app_event_show', ['id' => $eventId]);
        }

        return $this->render('account_event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }
}
