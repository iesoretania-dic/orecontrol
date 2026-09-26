<?php

namespace App\Service;

use App\Entity\RuleGroup;
use App\Repository\RuleGroupRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UniFiAPIService
{
    public function __construct(
        private RuleGroupRepository $ruleGroupRepository,
        private HttpClientInterface $client,
        private ContainerBagInterface $containerBag) {
    }

    /**
     * Lists the firewall groups that currently exist on the UniFi controller, so an
     * admin can import one instead of copying its site_id/_id by hand.
     *
     * @return array<int, array{_id?: string, site_id?: string, name?: string, group_type?: string}>
     *
     * @throws \RuntimeException if the controller cannot be reached
     */
    public function listFirewallGroups(): array
    {
        try {
            $response = $this->client->request(
                'GET',
                'https://' . $this->containerBag->get('unifi.server_host') . '/proxy/network/api/s/default/rest/firewallgroup',
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'X-API-KEY' => $this->containerBag->get('unifi.api_key'),
                    ],
                    'verify_peer' => false,
                    'verify_host' => false,
                    'timeout' => 10,
                ]
            );

            $data = $response->toArray();
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException('No se ha podido conectar con el controlador UniFi.', previous: $e);
        }

        return $data['data'] ?? [];
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

            $this->putFirewallGroup($ruleGroup, $networkList);
        }
    }

    /**
     * Fetches a rule group's current data straight from UniFi, including its live
     * group_members: the app's own database never stores that list.
     *
     * @return array{group_members?: string[]}
     *
     * @throws \RuntimeException if the controller cannot be reached or the group no longer exists there
     */
    public function getFirewallGroup(RuleGroup $ruleGroup): array
    {
        try {
            $response = $this->client->request(
                'GET',
                'https://' . $this->containerBag->get('unifi.server_host') . '/proxy/network/api/s/default/rest/firewallgroup/' . $ruleGroup->get_id(),
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'X-API-KEY' => $this->containerBag->get('unifi.api_key'),
                    ],
                    'verify_peer' => false,
                    'verify_host' => false,
                    'timeout' => 10,
                ]
            );

            $data = $response->toArray();
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException('No se ha podido conectar con el controlador UniFi.', previous: $e);
        }

        return $data['data'][0] ?? [];
    }

    /**
     * Creates a brand new firewall group on the UniFi controller, so an address-group
     * can be registered from the app instead of requiring the admin to create it in
     * the UniFi UI first and then come back to copy its site_id/_id.
     *
     * @param string[] $members
     *
     * @return array{_id: string, site_id: string, name: string, group_type: string}
     *
     * @throws \RuntimeException if the controller cannot be reached or refuses the request
     */
    public function createFirewallGroup(string $name, string $groupType, array $members = []): array
    {
        try {
            $response = $this->client->request(
                'POST',
                'https://' . $this->containerBag->get('unifi.server_host') . '/proxy/network/api/s/default/rest/firewallgroup',
                [
                    'json' => [
                        'name' => $name,
                        'group_type' => $groupType,
                        'group_members' => $members,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'X-API-KEY' => $this->containerBag->get('unifi.api_key'),
                    ],
                    'verify_peer' => false,
                    'verify_host' => false,
                    'timeout' => 10,
                ]
            );

            $data = $response->toArray();
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException('No se ha podido crear el grupo en el controlador UniFi.', previous: $e);
        }

        $created = $data['data'][0] ?? null;
        if ($created === null || !isset($created['_id'])) {
            throw new \RuntimeException('El controlador UniFi no ha devuelto el grupo creado.');
        }

        return $created;
    }

    /**
     * Replaces a rule group's member list (IPs/CIDRs) directly on the UniFi controller.
     *
     * @param string[] $members
     *
     * @throws \RuntimeException if the controller cannot be reached
     */
    public function setGroupMembers(RuleGroup $ruleGroup, array $members): void
    {
        try {
            $this->putFirewallGroup($ruleGroup, $members);
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException('No se ha podido guardar en el controlador UniFi.', previous: $e);
        }
    }

    /**
     * @param string[] $groupMembers
     */
    private function putFirewallGroup(RuleGroup $ruleGroup, array $groupMembers): void
    {
        $this->client->request(
            'PUT',
            'https://' . $this->containerBag->get('unifi.server_host') . '/proxy/network/api/s/default/rest/firewallgroup/' . $ruleGroup->get_id(),
            [
                'json' => [
                    '_id' => $ruleGroup->get_id(),
                    'name' => $ruleGroup->getName(),
                    'group_type' => $ruleGroup->getGroupType(),
                    'site_id' => $ruleGroup->getSiteId(),
                    'group_members' => $groupMembers,
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'X-API-KEY' => $this->containerBag->get('unifi.api_key'),
                ],
                'verify_peer' => false,
                'verify_host' => false,
                'timeout' => 10,
            ]
        );
    }
}
