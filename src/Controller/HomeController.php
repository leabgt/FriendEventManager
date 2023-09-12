<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EventRepository $eventRepository, Security $security, UserRepository $userRepository): Response
    {
        $user = $security->getUser();
        $userevents = null;

        if ($user) {
            $userRepo = $userRepository->find($this->getUser());
            $userevents = $userRepo->getEvents();
        }



        return $this->render('home/index.html.twig', [
            'events' => $eventRepository->findAll(),
            'user' => $user,
            'userevents' => $userevents,
        ]);
    }
}
