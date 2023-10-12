<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\RegistrationRepository;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;  // Import TokenStorageInterface

class ParticipationController extends AbstractController
{
    #[Route('/participation', name: 'app_participation')]
    public function index(): Response
    {
        return $this->render('participation/index.html.twig', [
            'controller_name' => 'ParticipationController',
        ]);
    }

    #[Route('/participation/{eventId}', name: 'app_participation_contribute')]
    public function contributeToEvent(int $eventId, EventRepository $eventRepository, RegistrationRepository $registrationRepository, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        $user = $tokenStorage->getToken()->getUser();

        $event = $eventRepository->find($eventId);
        if (!$event) {
            throw $this->createNotFoundException('L\'événement n\'a pas été trouvé.');
        }

        $registration = $registrationRepository->findOneBy(['user' => $user, 'event' => $eventId]);

        if (!$registration) {
            $this->addFlash('error', 'Inscription non trouvée pour cet événement.');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        if ($registration->isHasParticipated() == true) {
            $this->addFlash('error', 'Vous avez déjà contribué à cette cagnotte.');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        $amount = $event->getFinancialParticipationAmount();
        $registration->setHasParticipated(true);

        $currentAmount = $event->getTotalAmountCollected();
        $newAmount = $currentAmount + $amount;
        $event->setTotalAmountCollected($newAmount);
        $entityManager->persist($event);

        $entityManager->persist($registration);
        $entityManager->flush();

        $this->addFlash('success', 'Votre contribution financière a été mise à jour avec succès.');
        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }
}
