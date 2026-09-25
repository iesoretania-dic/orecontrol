<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\RuleLogRepository;
use App\Service\NetworkAccessService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RuleLogController extends AbstractController
{
    private const PER_PAGE = 20;

    #[Route('/historial', name: 'rule_log_index')]
    public function index(Request $request, NetworkAccessService $networkAccessService, RuleLogRepository $ruleLogRepository): Response
    {
        $networks = $networkAccessService->getManagedNetworks((string) $request->getClientIp());

        if ($networks === []) {
            return $this->render('rule_log/index.html.twig', [
                'networks' => [],
                'selected_network' => null,
                'logs' => [],
                'page' => 1,
                'last_page' => 1,
            ]);
        }

        $requestedId = $request->query->getInt('network', 0);
        $selectedNetwork = null;
        foreach ($networks as $network) {
            if ($network->getId() === $requestedId) {
                $selectedNetwork = $network;
                break;
            }
        }
        $selectedNetwork ??= $networks[0];

        $total = $ruleLogRepository->countForNetworks([$selectedNetwork]);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min(max(1, $request->query->getInt('page', 1)), $lastPage);

        $logs = $ruleLogRepository->findForNetworks([$selectedNetwork], ($page - 1) * self::PER_PAGE, self::PER_PAGE);

        return $this->render('rule_log/index.html.twig', [
            'networks' => $networks,
            'selected_network' => $selectedNetwork,
            'logs' => $logs,
            'page' => $page,
            'last_page' => $lastPage,
        ]);
    }
}
