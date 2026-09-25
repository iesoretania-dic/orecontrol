<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Network;
use App\Entity\Person;
use App\Entity\RuleGroup;
use App\Repository\NetworkRepository;

class NetworkRuleGroupService
{
    public function __construct(
        private readonly NetworkRepository $networkRepository,
        private readonly UniFiAPIService $uniFiAPIService,
    ) {
    }

    public function assign(Network $network, ?RuleGroup $ruleGroup, ?Person $enabledBy, ?string $enabledIp): void
    {
        $network->setRuleGroup($ruleGroup);

        if ($ruleGroup !== null) {
            $network->setEnabledAt(new \DateTimeImmutable());
            $network->setEnabledIp($enabledIp);
            $network->setEnabledBy($enabledBy);
        } else {
            $network->setEnabledAt(null);
            $network->setEnabledIp(null);
            $network->setEnabledBy(null);
        }

        $this->networkRepository->save($network, true);
    }

    /** Call once after applying all the changes of a batch. */
    public function pushToUniFi(): void
    {
        $this->uniFiAPIService->updateRuleGroups();
    }
}
