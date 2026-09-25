<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Network;
use App\Repository\NetworkRepository;
use Symfony\Bundle\SecurityBundle\Security;

class NetworkAccessService
{
    public function __construct(
        private readonly NetworkRepository $networkRepository,
        private readonly Security $security,
    ) {
    }

    /**
     * @return Network[] Networks the current visitor is allowed to change: all of them if
     *                    logged in as a teacher, otherwise only the ones matching their IP.
     */
    public function getManagedNetworks(string $ip): array
    {
        if ($this->security->isGranted('ROLE_TEACHER')) {
            return $this->networkRepository->findAllOrdered();
        }

        return $this->networkRepository->findByAllowedIp($ip);
    }

    public function canManage(Network $network, string $ip): bool
    {
        return in_array($network, $this->getManagedNetworks($ip), true);
    }
}
