<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ActiveRule;
use App\Entity\Network;
use App\Repository\ActiveRuleRepository;
use App\Repository\NetworkRepository;
use App\Repository\RuleGroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use function Zenstruck\Foundry\factory;

class FrontpageController extends AbstractController
{

    public function __construct(
        private NetworkRepository $networkRepository,
        private RuleGroupRepository $ruleGroupRepository,
        private ActiveRuleRepository $activeRuleRepository,
    ) {
    }

    #[Route('/', name: 'frontpage')]
    public function index(Request $request): Response
    {
        $ip = $request->getClientIp();
        $networks = $this->networkRepository->findAllOrdered();
        $networksManaged = $this->networkRepository->findByAllowedIp($ip);
        $ruleGroups = $this->ruleGroupRepository->findAllSelectable();

        return $this->render('frontpage/index.html.twig', [
            'networks' => $networks,
            'networks_managed' => $networksManaged,
            'ip' => $ip,
            'rule_groups' => $ruleGroups
        ]);
    }

    #[Route('/update/{id}', name: 'frontpage_update', methods: ['POST'])]
    public function setRuleGroup(Request $request, Network $network): Response
    {
        $ip = $request->getClientIp();
        $networksManaged = $this->networkRepository->findByAllowedIp($ip);

        if (!in_array($network, $networksManaged, true)) {
            throw $this->createAccessDeniedException();
        }

        $ruleGroup = $this->ruleGroupRepository->find($request->request->get('rule_group'));

        if ($network->getActiveRule()) {
            $activeRule = $network->getActiveRule();
            if ($ruleGroup === null) {
                $this->activeRuleRepository->remove($network->getActiveRule());
                $network->setActiveRule(null);
            } else {
                $activeRule->setRuleGroup($ruleGroup);
                $this->activeRuleRepository->save($activeRule);
            }
            $this->networkRepository->save($network, true);
        } else if ($ruleGroup !== null) {
            $activeRule = new ActiveRule();
            $activeRule->setNetwork($network);
            $activeRule->setRuleGroup($ruleGroup);

            $network->setActiveRule($activeRule);
            $activeRule->setCreatedAt(new \DateTimeImmutable());
            $activeRule->setIp($ip);
            $this->networkRepository->save($network, true);
            $this->activeRuleRepository->save($activeRule, true);
        }

        return $this->redirectToRoute('frontpage', []);
    }
}
