<?php

namespace App\Command;

use App\Entity\Event;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

        for ($i = 0; $i < 50; $i++) {
            $event = new Event();

            $event->setTitle($faker->sentence)
                ->setStartDate($faker->dateTimeBetween('-1 years', 'now'))
                ->setEndDate($faker->dateTimeBetween('now', '+1 years'))
                ->setIsPrivate($faker->boolean)
                ->setIsFinancialParticipation($faker->boolean)
                ->setFinancialParticipationAmount($faker->randomFloat(2, 5, 200))
                ->setPlace($faker->address);

            // Assurez-vous d'avoir au moins une catégorie dans votre BD
            $category = $this->entityManager->getRepository(Category::class)->findOneBy([]);
            if ($category) {
                $event->setCategory($category);
            } else {
                // Si aucune catégorie n'est trouvée, vous pouvez créer une nouvelle catégorie ici.
                $category = new Category();
                $category->setName($faker->word);
                $this->entityManager->persist($category);
                $event->setCategory($category);
            }

            // Assurez-vous d'avoir au moins un organisateur dans votre BD
            $organisator = $this->entityManager->getRepository(User::class)->findOneBy([]);
            if ($organisator) {
                $event->setOrganisator($organisator);
            }

            $this->entityManager->persist($event);
        }

        $this->entityManager->flush();

        $output->writeln("50 events have been generated!");

        return Command::SUCCESS;
    }
}
