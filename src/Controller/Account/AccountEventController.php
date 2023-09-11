<?php

namespace App\Controller\Account;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use App\Form\EventType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/mon-compte/evenements')]
class AccountEventController extends AbstractController
{
    #[Route('/', name: 'app_account_myevents')]
    public function index(Security $security, UserRepository $userRepository): Response
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

    #[Route('/new', name: 'app_account_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EventRepository $eventRepository): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventRepository->save($event, true);

            return $this->redirectToRoute('app_account_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }
}
