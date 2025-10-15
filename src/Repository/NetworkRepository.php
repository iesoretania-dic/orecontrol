<?php

namespace App\Repository;

use App\Entity\Network;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Network>
 */
class NetworkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Network::class);
    }

    public function findByAllowedIp(string $ip): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.allowed_ip = :ip')
            ->setParameter('ip', $ip)
            ->orderBy('n.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('n')
            ->orderBy('n.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
