<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\NetworkRepository;
use App\Repository\RuleGroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FrontpageController extends AbstractController
{

    public function __construct(
        private NetworkRepository $networkRepository,
        private RuleGroupRepository $ruleGroupRepository
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
}
