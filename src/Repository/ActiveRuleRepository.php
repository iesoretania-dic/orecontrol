<?php

namespace App\Repository;

use App\Entity\ActiveRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ActiveRule>
 */
class ActiveRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActiveRule::class);
    }

    public function remove(ActiveRule $getActiveRule, bool $flush = false): void
    {
        $this->getEntityManager()->remove($getActiveRule);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function save(ActiveRule $getActiveRule, bool $flush = false): void
    {
        $this->getEntityManager()->persist($getActiveRule);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
