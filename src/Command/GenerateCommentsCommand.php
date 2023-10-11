<?php

namespace App\Command;

use App\Entity\Comment;
use App\Entity\Event;
use App\Entity\Registration;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommentsCommand extends Command
{
    protected static $defaultName = 'app:generate-comments';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->setDescription('Generate fake comments for random events by users with confirmed registration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $faker = Factory::create();

        for ($i = 0; $i < 100; $i++) {
            $events = $this->entityManager->getRepository(Event::class)->findAll();
            $event = $events[array_rand($events)];

            $registrations = $this->entityManager->getRepository(Registration::class)
                ->findBy(['event' => $event, 'hasConfirmed' => true]);

            if ($registrations) {
                $registration = $registrations[array_rand($registrations)];
                $user = $registration->getUser();

                $comment = new Comment();
                $comment->setEvent($event);
                $comment->setUser($user);
                $comment->setComment($faker->sentence);
                $comment->setCreatedAt(new \DateTimeImmutable());

                $this->entityManager->persist($comment);
            } else {
                $i--; // retry with another event if no suitable user found
            }
        }

        $this->entityManager->flush();
        $output->writeln("100 comments have been generated!");

        return Command::SUCCESS;
    }
}
