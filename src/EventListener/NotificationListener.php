<?php

namespace App\EventListener;

use App\Repository\NotificationRepository;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class NotificationListener
{
    private $twig;
    private $notificationRepository;
    private $tokenStorage; 

    public function __construct(Environment $twig, NotificationRepository $notificationRepository, TokenStorageInterface $tokenStorage)
    {
        $this->twig = $twig;
        $this->notificationRepository = $notificationRepository;
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        $user = null;

        if ($token && $token->getUser() instanceof UserInterface) {
            $user = $token->getUser();
        }

        if ($user) {
            $allNotifications = $this->notificationRepository->findBy(['user' => $user]);
            $unreadNotifications = $this->notificationRepository->findBy(['user' => $user, 'isRead' => false]);

            $this->twig->addGlobal('allNotifications', $allNotifications);
            $this->twig->addGlobal('unreadNotifications', $unreadNotifications);
        }
    }
}
