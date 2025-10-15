<?php

namespace App\Service;

use App\Repository\RuleGroupRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UniFiAPIService
{
    public function __construct(
        private RuleGroupRepository $ruleGroupRepository,
        private HttpClientInterface $client,
        private ContainerBagInterface $containerBag) {
    }

    public function updateRuleGroups() {
        $ruleGroups = $this->ruleGroupRepository->findAllSelectable();
        foreach ($ruleGroups as $ruleGroup) {
            $networks = $ruleGroup->getNetworks();
            if (count($networks) === 0) {
                $networkList = ['0.0.0.1'];
            } else {
                $networkList = [];
                foreach ($networks as $network) {
                    $networkList[] = $network->getRuleDescription();
                }
            }
            $response = $this->client->request(
                'PUT',
                'https://' . $this->containerBag->get('unifi.server_host') . '/proxy/network/api/s/default/rest/firewallgroup/' . $ruleGroup->get_Id(),
                [
                    'json' => [
                        '_id' => $ruleGroup->get_id(),
                        'name' => $ruleGroup->getName(),
                        'group_type' => $ruleGroup->getGroupType(),
                        'site_id' => $ruleGroup->getSiteId(),
                        'group_members' => $networkList
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'X-API-KEY' => $this->containerBag->get('unifi.api_key')
                    ],
                    'verify_peer' => false,
                    'verify_host' => false
                ]
            );
        }
    }
}
