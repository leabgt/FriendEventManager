<?php

namespace App\Command;

use App\Entity\Registration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateRegistrationParticipationCommand extends Command
{
    protected static $defaultName = 'app:update-registration-participation';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->setDescription('Update isHasParticipated for all confirmed registrations.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $registrations = $this->entityManager->getRepository(Registration::class)->findBy(['hasConfirmed' => true]);

        if (!$registrations) {
            $output->writeln('No confirmed registrations found!');
            return Command::FAILURE;
        }

        foreach ($registrations as $registration) {
            $registration->setHasParticipated((bool) random_int(0, 1)); 
        }

        $this->entityManager->flush();

        $output->writeln('Participation status updated for all confirmed registrations!');
        return Command::SUCCESS;
    }
}
