<?php

namespace App\Repository;

use App\Entity\RuleGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RuleGroup>
 */
class RuleGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RuleGroup::class);
    }

    public function findAllSelectable(): array
    {
        return $this->createQueryBuilder('rg')
            ->andWhere('rg.selectable = :val')
            ->setParameter('val', true)
            ->orderBy('rg.name', \SortDirection::Ascending)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return RuleGroup[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('rg')
            ->orderBy('rg.name', \SortDirection::Ascending)
            ->getQuery()
            ->getResult();
    }

    public function save(RuleGroup $ruleGroup, bool $flush = false): void
    {
        $this->getEntityManager()->persist($ruleGroup);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RuleGroup $ruleGroup, bool $flush = false): void
    {
        $this->getEntityManager()->remove($ruleGroup);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
