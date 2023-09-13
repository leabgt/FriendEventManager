<?php

namespace App\Controller;

use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class NotificationController extends AbstractController
{
    #[Route('/notification/read', name: 'app_notification_read')]
    public function markAsRead(Request $request, NotificationRepository $notificationRepository, EntityManagerInterface $entityManager)
    {
        $notificationId = $request->request->get('id');

        $notification = $notificationRepository->find($notificationId);
        if (!$notification) {
            return new JsonResponse(['status' => 'error', 'message' => 'Notification not found'], 404);
        }

        $notification->setIsRead(true);
        $entityManager->persist($notification);
        $entityManager->flush();

        return new JsonResponse(['status' => 'success', 'message' => 'Notification marked as read']);
    }
}
