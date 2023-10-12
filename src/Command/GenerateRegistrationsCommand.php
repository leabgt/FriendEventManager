<?php

namespace App\Command;

use App\Entity\Event;
use App\Entity\Registration;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateRegistrationsCommand extends Command
{
    protected static $defaultName = 'app:generate-registrations';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->setDescription('Generate random registrations.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        for ($i = 0; $i < 200; $i++) {
            $eventCount = $this->entityManager->createQuery('SELECT COUNT(e) FROM App\Entity\Event e')->getSingleScalarResult();
            $randomEventOffset = rand(0, $eventCount - 1);
            $event = $this->entityManager->createQuery('SELECT e FROM App\Entity\Event e')
                ->setMaxResults(1)
                ->setFirstResult($randomEventOffset)
                ->getOneOrNullResult();

            if (!$event) {
                $output->writeln("No event found!");
                return Command::FAILURE;
            }

            $userCount = $this->entityManager->createQuery('SELECT COUNT(u) FROM App\Entity\User u WHERE u.id != :organizerId')
                ->setParameter('organizerId', $event->getOrganisator()->getId())
                ->getSingleScalarResult();
            $randomUserOffset = rand(0, $userCount - 1);
            $user = $this->entityManager->createQuery('SELECT u FROM App\Entity\User u WHERE u.id != :organizerId')
                ->setParameter('organizerId', $event->getOrganisator()->getId())
                ->setMaxResults(1)
                ->setFirstResult($randomUserOffset)
                ->getOneOrNullResult();

            if (!$user) {
                $output->writeln("No suitable user found!");
                return Command::FAILURE;
            }

            $registration = new Registration();
            $registration->setEvent($event);
            $registration->setRegistrationDate(new \DateTime());
            $registration->setUser($user);
            $registration->setIsInvited(true);
            $registration->setHasConfirmed((bool)random_int(0, 1));


            $this->entityManager->persist($registration);

            if ($i % 20 === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }

        $this->entityManager->flush();

        $output->writeln("200 registrations have been generated!");
        return Command::SUCCESS;
    }
}
