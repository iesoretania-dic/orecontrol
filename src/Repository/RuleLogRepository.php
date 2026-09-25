<?php

namespace App\Repository;

use App\Entity\Network;
use App\Entity\RuleLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RuleLog>
 */
class RuleLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RuleLog::class);
    }

    /** The still-open period (not yet superseded by another change) for a network, if any. */
    public function findOpenForNetwork(Network $network): ?RuleLog
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.network = :network')
            ->andWhere('r.deletedAt IS NULL')
            ->setParameter('network', $network)
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return RuleLog[]
     */
    public function findAllOrdered(int $limit = 200): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function save(RuleLog $ruleLog, bool $flush = false): void
    {
        $this->getEntityManager()->persist($ruleLog);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
