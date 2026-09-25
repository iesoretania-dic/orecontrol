<?php

namespace App\Repository;

use App\Entity\ScheduledTask;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScheduledTask>
 */
class ScheduledTaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScheduledTask::class);
    }

    /**
     * @return ScheduledTask[]
     */
    public function findEnabled(): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.enabled = :enabled')
            ->setParameter('enabled', true)
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return ScheduledTask[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(ScheduledTask $task, bool $flush = false): void
    {
        $this->getEntityManager()->persist($task);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ScheduledTask $task, bool $flush = false): void
    {
        $this->getEntityManager()->remove($task);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
