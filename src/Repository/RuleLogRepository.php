<?php

namespace App\Repository;

use App\Entity\Network;
use App\Entity\RuleGroup;
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
            ->orderBy('r.createdAt', \SortDirection::Descending)
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
            ->orderBy('r.createdAt', \SortDirection::Descending)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Network[] $networks
     */
    public function countForNetworks(array $networks): int
    {
        if ($networks === []) {
            return 0;
        }

        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.network IN (:networks)')
            ->setParameter('networks', $networks)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param Network[] $networks
     * @return RuleLog[]
     */
    public function findForNetworks(array $networks, int $offset, int $limit): array
    {
        if ($networks === []) {
            return [];
        }

        return $this->createQueryBuilder('r')
            ->andWhere('r.network IN (:networks)')
            ->setParameter('networks', $networks)
            ->orderBy('r.createdAt', \SortDirection::Descending)
            ->setFirstResult($offset)
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

    public function existsForRuleGroup(RuleGroup $ruleGroup): bool
    {
        return (bool) $this->createQueryBuilder('r')
            ->select('1')
            ->andWhere('r.ruleGroup = :ruleGroup')
            ->setParameter('ruleGroup', $ruleGroup)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
