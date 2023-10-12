<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Stripe\Stripe;
use Stripe\Customer;

class GenerateUsersCommand extends Command
{
    protected static $defaultName = 'app:generate-users';

    private $entityManager;

    // Suppression de la dépendance à UserPasswordEncoderInterface dans le constructeur
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

        $stripeSecretKey = $_ENV['STRIPE_SECRET_KEY'];

        Stripe::setApiKey($stripeSecretKey);

        for ($i = 0; $i < 50; $i++) {
            $user = new User();

            $password = password_hash('securePassword', PASSWORD_BCRYPT);

            $stripeCustomer = Customer::create([
                'email' => $user->getEmail(),
                'name' => $user->getFirstName() . ' ' . $user->getLastName(),
            ]);

            $user->setEmail($faker->unique()->safeEmail)
                ->setRoles(['ROLE_USER'])
                ->setPassword($password)
                ->setFirstName($faker->firstName)
                ->setLastName($faker->lastName)
                ->setBirthDate($faker->dateTimeBetween('-90 years', '-18 years'))
                ->setStripeCustomerId($stripeCustomer->id);

            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();

        $output->writeln("50 users have been generated!");

        return Command::SUCCESS;
    }
}
