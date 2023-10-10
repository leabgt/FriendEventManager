<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function save(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Event[] Returns an array of Event objects
     */
    public function findEventsByUser(User $user)
    {
        return $this->createQueryBuilder('e')
            ->join('e.registrations', 'r')
            ->where('r.user = :user')
            ->andWhere('r.hasConfirmed = :hasConfirmed')
            ->setParameters([
                'user' => $user,
                'hasConfirmed' => true,
            ])
            ->getQuery()
            ->getResult();
    }

    public function findEventsWithoutUnconfirmedRegistration(User $user)
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.registrations', 'r', 'WITH', 'r.user = :user')
            ->where('r.user IS NULL OR r.hasConfirmed = :hasConfirmed')
            ->setParameters([
                'user' => $user,
                'hasConfirmed' => false,
            ])
            ->getQuery()
            ->getResult();
    }

    public function findLimitedEvents()
    {
        return $this->createQueryBuilder('e')
            ->where('e.isPrivate = :isPrivate')
            ->setParameter('isPrivate', false)
            ->getQuery()
            ->getResult();
    }
}
