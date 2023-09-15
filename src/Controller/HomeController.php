<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use App\Service\DateFormatterService;

class HomeController extends AbstractController
{
    // private $dateFormatterService;

    // public function __construct(DateFormatterService $dateFormatterService)
    // {
    //     $this->dateFormatterService = $dateFormatterService;
    // }

    #[Route('/', name: 'app_home')]
    public function index(EventRepository $eventRepository, Security $security, UserRepository $userRepository, DateFormatterService $dateFormatter): Response
    {
        setlocale(LC_TIME, 'fr_FR.UTF-8');

        $events = $eventRepository->findAll();

        $user = $security->getUser();
        $userevents = null;

        if ($user) {
            $userRepo = $userRepository->find($this->getUser());
            $userevents = $userRepo->getEvents();
        }

        return $this->render('home/index.html.twig', [
            'events' => $events,
            'user' => $user,
            'userevents' => $userevents,
            'dateFormatter' => $dateFormatter, // Passer le service de formatage à la vue
        ]);
    }
}
