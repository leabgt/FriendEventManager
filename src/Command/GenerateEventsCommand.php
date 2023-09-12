<?php

namespace App\Command;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Faker\Factory;

class GenerateEventsCommand extends Command
{
    protected static $defaultName = 'app:generate-events';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->setDescription('Generate fake events.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $faker = Factory::create();

        // Assurez-vous d'avoir au moins un utilisateur et une catégorie dans votre BD
        // Sinon, cette partie du code échouera
        $organisator = $this->entityManager->getRepository(User::class)->findOneBy([]);
        $category = $this->entityManager->getRepository(Category::class)->findOneBy([]);

        if (!$organisator || !$category) {
            $output->writeln("Please ensure you have at least one User and one Category in your database.");
            return Command::FAILURE;
        }

        for ($i = 0; $i < 50; $i++) {
            $event = new Event();

            $event->setTitle($faker->sentence)
                ->setMaxContributor($faker->numberBetween(1, 100))
                ->setMinContributor($faker->numberBetween(1, 50))
                ->setStartDate($faker->dateTimeBetween('-1 years', 'now'))
                ->setEndDate($faker->dateTimeBetween('now', '+1 years'))
                ->setIsPrivate($faker->boolean)
                ->setIsFinancialParticipation($faker->boolean)
                ->setFinancialParticipationAmount($faker->randomFloat(2, 5, 200))
                ->setCategory($category)
                ->setOrganisator($organisator)
                ->setPlace($faker->address);

            $this->entityManager->persist($event);
        }

        $this->entityManager->flush();

        $output->writeln("50 events have been generated!");

        return Command::SUCCESS;
    }
}
