<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Network;
use App\Entity\RuleGroup;
use App\Repository\NetworkRepository;
use App\Repository\RuleGroupRepository;
use App\Service\NetworkAccessService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('NetworkGrid')]
final class NetworkGrid
{
    use DefaultActionTrait;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly NetworkRepository $networkRepository,
        private readonly RuleGroupRepository $ruleGroupRepository,
        private readonly NetworkAccessService $networkAccessService,
    ) {
    }

    public function getIp(): string
    {
        return (string) $this->requestStack->getCurrentRequest()?->getClientIp();
    }

    /** @return Network[] */
    public function getNetworksManaged(): array
    {
        return $this->networkAccessService->getManagedNetworks($this->getIp());
    }

    /** @return Network[] */
    public function getNetworks(): array
    {
        $managed = $this->getNetworksManaged();
        $all = $this->networkRepository->findAllOrdered();

        return array_unique(array_merge($managed, $all), SORT_REGULAR);
    }

    /** @return RuleGroup[] */
    public function getRuleGroups(): array
    {
        return $this->ruleGroupRepository->findAllSelectable();
    }
}
