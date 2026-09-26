<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Network;
use App\Entity\Person;
use App\Entity\RuleGroup;
use App\Entity\RuleLog;
use App\Entity\ScheduledTask;
use App\Repository\NetworkRepository;
use App\Repository\RuleLogRepository;

class NetworkRuleGroupService
{
    public function __construct(
        private readonly NetworkRepository $networkRepository,
        private readonly RuleLogRepository $ruleLogRepository,
        private readonly UniFiAPIService $uniFiAPIService,
    ) {
    }

    public function assign(Network $network, ?RuleGroup $ruleGroup, ?Person $enabledBy, ?string $enabledIp, ?ScheduledTask $scheduledTask = null): void
    {
        if ($network->getRuleGroup() !== $ruleGroup) {
            $this->logChange($network, $ruleGroup, $enabledBy, $enabledIp, $scheduledTask);
        }

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

    /**
     * Closes whatever period was open for the network (if any) and opens a new one, so
     * rule_log always shows who/where activated or deactivated a rule, and when. A change
     * made from an authorised IP without a logged-in teacher leaves the "who" side null.
     */
    private function logChange(Network $network, ?RuleGroup $ruleGroup, ?Person $actor, ?string $actorIp, ?ScheduledTask $scheduledTask): void
    {
        $now = new \DateTimeImmutable();

        $openLog = $this->ruleLogRepository->findOpenForNetwork($network);
        if ($openLog !== null) {
            $openLog->setDeletedAt($now);
            $openLog->setDeletedBy($actor);
            $openLog->setDeletedIp($actorIp);
            $openLog->setDeletedScheduledTask($scheduledTask);
            $openLog->setDeletedViaScheduledTask($scheduledTask !== null);
            $this->ruleLogRepository->save($openLog);
        }

        $newLog = new RuleLog();
        $newLog->setNetwork($network);
        $newLog->setRuleGroup($ruleGroup);
        $newLog->setCreatedAt($now);
        $newLog->setCreatedBy($actor);
        $newLog->setCreatedIp($actorIp);
        $newLog->setCreatedScheduledTask($scheduledTask);
        $newLog->setCreatedViaScheduledTask($scheduledTask !== null);
        $this->ruleLogRepository->save($newLog);
    }

    /** Call once after applying all the changes of a batch. */
    public function pushToUniFi(): void
    {
        $this->uniFiAPIService->updateRuleGroups();
    }
}
