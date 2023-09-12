<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Faker\Factory;

class GenerateUsersCommand extends Command
{
    protected static $defaultName = 'app:generate-users';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->setDescription('Generate fake users.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $faker = Factory::create();

        for ($i = 0; $i < 50; $i++) {
            $user = new User();

            $user->setEmail($faker->unique()->safeEmail)
                ->setRoles(['ROLE_USER'])
                ->setPassword('some_hashed_password') // N'oubliez pas de hasher le mot de passe
                ->setFirstName($faker->firstName)
                ->setLastName($faker->lastName)
                ->setBirthDate($faker->dateTimeBetween('-100 years', '-18 years'));

            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();

        $output->writeln("50 users have been generated!");

        return Command::SUCCESS;
    }
}
