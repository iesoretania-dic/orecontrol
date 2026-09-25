<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Network;
use App\Entity\Person;
use App\Repository\RuleGroupRepository;
use App\Service\NetworkAccessService;
use App\Service\NetworkRuleGroupService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

class FrontpageController extends AbstractController
{
    public function __construct(
        private RuleGroupRepository $ruleGroupRepository,
        private NetworkAccessService $networkAccessService,
        private NetworkRuleGroupService $networkRuleGroupService,
    ) {
    }

    #[Route('/', name: 'frontpage')]
    public function index(): Response
    {
        return $this->render('frontpage/index.html.twig');
    }

    #[IsCsrfTokenValid('network_update', tokenKey: '_token')]
    #[Route('/update/{id}', name: 'frontpage_update', methods: ['POST'])]
    public function setRuleGroup(Request $request, Network $network): Response
    {
        $ip = $request->getClientIp();

        if (!$this->networkAccessService->canManage($network, $ip)) {
            throw $this->createAccessDeniedException();
        }

        $ruleGroup = $this->ruleGroupRepository->find($request->request->get('rule_group'));
        $user = $this->getUser();
        $person = $user instanceof Person ? $user : null;

        $this->networkRuleGroupService->assign($network, $ruleGroup, $person, $ip);
        $this->networkRuleGroupService->pushToUniFi();

        return $this->redirectToRoute('frontpage');
    }
}
