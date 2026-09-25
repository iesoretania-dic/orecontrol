<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\RuleGroup;
use App\Repository\RuleGroupRepository;
use App\Repository\RuleLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reglas')]
#[IsGranted('ROLE_ADMIN')]
class RuleGroupController extends AbstractController
{
    public function __construct(
        private readonly RuleGroupRepository $ruleGroupRepository,
        private readonly RuleLogRepository $ruleLogRepository,
    ) {
    }

    #[Route('', name: 'admin_rule_group_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/rule_group/index.html.twig', [
            'ruleGroups' => $this->ruleGroupRepository->findAllOrdered(),
        ]);
    }

    #[Route('/nueva', name: 'admin_rule_group_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->handleForm($request, new RuleGroup());
    }

    #[Route('/{id}/editar', name: 'admin_rule_group_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RuleGroup $ruleGroup): Response
    {
        return $this->handleForm($request, $ruleGroup);
    }

    #[IsCsrfTokenValid('delete_rule_group', tokenKey: '_token')]
    #[Route('/{id}/eliminar', name: 'admin_rule_group_delete', methods: ['POST'])]
    public function delete(RuleGroup $ruleGroup): Response
    {
        if (!$ruleGroup->getNetworks()->isEmpty()) {
            $this->addFlash('error', sprintf(
                'No se puede eliminar "%s": hay aulas que la tienen asignada actualmente.',
                $ruleGroup->getName()
            ));

            return $this->redirectToRoute('admin_rule_group_index');
        }

        if ($this->ruleLogRepository->existsForRuleGroup($ruleGroup)) {
            $this->addFlash('error', sprintf(
                'No se puede eliminar "%s": aparece en el historial de accesos.',
                $ruleGroup->getName()
            ));

            return $this->redirectToRoute('admin_rule_group_index');
        }

        $this->ruleGroupRepository->remove($ruleGroup, true);
        $this->addFlash('success', sprintf('Regla "%s" eliminada.', $ruleGroup->getName()));

        return $this->redirectToRoute('admin_rule_group_index');
    }

    private function handleForm(Request $request, RuleGroup $ruleGroup): Response
    {
        $isNew = $ruleGroup->getId() === null;
        $errors = [];

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('rule_group_form', (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }

            $name = trim((string) $request->request->get('name', ''));
            $siteId = trim((string) $request->request->get('site_id', ''));
            $uniFiId = trim((string) $request->request->get('unifi_id', ''));
            $groupType = trim((string) $request->request->get('group_type', ''));
            $selectable = $request->request->getBoolean('selectable', true);

            if ($name === '') {
                $errors[] = 'El nombre no puede estar vacío.';
            }
            if ($siteId === '') {
                $errors[] = 'El "site_id" no puede estar vacío.';
            }
            if ($uniFiId === '') {
                $errors[] = 'El identificador de UniFi ("_id") no puede estar vacío.';
            }
            if ($groupType === '') {
                $errors[] = 'El tipo de grupo no puede estar vacío.';
            }

            if ($errors === []) {
                $ruleGroup->setName($name);
                $ruleGroup->setSiteId($siteId);
                $ruleGroup->set_id($uniFiId);
                $ruleGroup->setGroupType($groupType);
                $ruleGroup->setSelectable($selectable);

                $this->ruleGroupRepository->save($ruleGroup, true);
                $this->addFlash('success', sprintf('Regla "%s" guardada.', $ruleGroup->getName()));

                return $this->redirectToRoute('admin_rule_group_index');
            }
        }

        return $this->render('admin/rule_group/form.html.twig', [
            'ruleGroup' => $ruleGroup,
            'is_new' => $isNew,
            'errors' => $errors,
        ]);
    }
}
