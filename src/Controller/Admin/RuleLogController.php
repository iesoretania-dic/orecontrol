<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\RuleLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/historial')]
#[IsGranted('ROLE_ADMIN')]
class RuleLogController extends AbstractController
{
    #[Route('', name: 'admin_rule_log_index', methods: ['GET'])]
    public function index(RuleLogRepository $ruleLogRepository): Response
    {
        return $this->render('admin/rule_log/index.html.twig', [
            'logs' => $ruleLogRepository->findAllOrdered(),
        ]);
    }
}
