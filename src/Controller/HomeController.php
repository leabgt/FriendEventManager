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
    #[Route('/', name: 'app_home')]
    public function index(EventRepository $eventRepository, Security $security, DateFormatterService $dateFormatter): Response
    {
        setlocale(LC_TIME, 'fr_FR.UTF-8');

        $user = $security->getUser();
        $events = $userevents = null;
        $limit = 8;  

        if ($user) {
            $userevents = $eventRepository->findEventsByUser($user, $limit); 
            $events = $eventRepository->findEventsWithoutUnconfirmedRegistration($user, $limit);  
        } else {
            $events = $eventRepository->findLimitedEvents($limit);  
        }

        return $this->render('home/index.html.twig', [
            'events' => $events,
            'user' => $user,
            'userevents' => $userevents,
            'dateFormatter' => $dateFormatter,
        ]);
    }
}
