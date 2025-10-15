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
            ->orderBy('rg.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
