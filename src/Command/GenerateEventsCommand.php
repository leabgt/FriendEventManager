<?php

namespace App\Command;

use App\Entity\Event;
use App\Entity\Category;
use App\Entity\User;
use App\Entity\Registration;
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

        for ($i = 0; $i < 20; $i++) {
            $event = new Event();

            $event->setTitle($faker->sentence)
                ->setStartDate($faker->dateTimeBetween('-1 years', 'now'))
                ->setEndDate($faker->dateTimeBetween('now', '+1 years'))
                ->setIsPrivate($faker->boolean)
                ->setIsFinancialParticipation($faker->boolean)
                ->setPlace($faker->address);

            if ($event->isIsFinancialParticipation()) {
                $event->setFinancialParticipationAmount($faker->randomFloat(2, 5, 200));
            }

            // Assurez-vous d'avoir au moins une catégorie dans votre BD
            $categories = $this->entityManager->getRepository(Category::class)->findAll();
            if ($categories) {
                $category = $categories[array_rand($categories)];
            } else {
                $category = new Category();
                $category->setName($faker->word);
                $this->entityManager->persist($category);
            }
            $event->setCategory($category);

            // Assurez-vous d'avoir au moins un organisateur dans votre BD
            $users = $this->entityManager->getRepository(User::class)->findAll();
            if ($users) {
                $organisator = $users[array_rand($users)];
                $event->setOrganisator($organisator);

                // Création de la Registration ici
                $registration = new Registration();
                $registration->setEvent($event);
                $registration->setUser($organisator); // Utiliser $organisator ici pour assurer que l'organisateur est l'utilisateur enregistré
                $registration->setHasConfirmed(true);
                $registration->setRegistrationDate(new \DateTime());
                $this->entityManager->persist($registration); // Utiliser $this->entityManager au lieu de $entityManager
            }

            $this->entityManager->persist($event);
        }

        $this->entityManager->flush();

        $output->writeln("20 events have been generated!");

        return Command::SUCCESS;
    }
}
