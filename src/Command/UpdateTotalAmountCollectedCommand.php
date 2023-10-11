<?php

namespace App\Command;

use App\Entity\Registration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateTotalAmountCollectedCommand extends Command
{
    protected static $defaultName = 'app:update-total-amount-collected';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->setDescription('Update totalAmountCollected for events with participating registrations.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get all registrations where hasParticipated is true
        $registrations = $this->entityManager->getRepository(Registration::class)->findBy(['hasParticipated' => true]);

        if (!$registrations) {
            $output->writeln('No participating registrations found!');
            return Command::FAILURE;
        }

        foreach ($registrations as $registration) {
            $event = $registration->getEvent();

            // Get the current totalAmountCollected
            $currentAmount = $event->getTotalAmountCollected() ?? 0;

            // Update totalAmountCollected
            $newAmount = $currentAmount + $event->getFinancialParticipationAmount();

            $event->setTotalAmountCollected($newAmount);
        }

        // Flush changes to database
        $this->entityManager->flush();

        $output->writeln('Total amount collected updated for all events with participating registrations!');
        return Command::SUCCESS;
    }
}