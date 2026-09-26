<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\RuleGroup;
use App\Repository\RuleGroupRepository;
use App\Repository\RuleLogRepository;
use App\Service\UniFiAPIService;
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
        private readonly UniFiAPIService $uniFiAPIService,
    ) {
    }

    #[Route('', name: 'admin_rule_group_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/rule_group/index.html.twig', [
            'ruleGroups' => $this->ruleGroupRepository->findAllOrdered(),
        ]);
    }

    #[Route('/importar', name: 'admin_rule_group_import', methods: ['GET', 'POST'])]
    public function import(Request $request): Response
    {
        $error = null;
        $remoteGroups = [];

        try {
            $remoteGroups = $this->uniFiAPIService->listFirewallGroups();
        } catch (\RuntimeException $e) {
            $error = $e->getMessage();
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('import_rule_group', (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }

            $uniFiId = (string) $request->request->get('unifi_id', '');
            $remote = null;
            foreach ($remoteGroups as $candidate) {
                if (($candidate['_id'] ?? null) === $uniFiId) {
                    $remote = $candidate;
                    break;
                }
            }

            if ($remote === null) {
                $this->addFlash('error', 'No se ha encontrado ese grupo en UniFi.');
            } elseif ($this->ruleGroupRepository->findOneBy(['_id' => $uniFiId]) !== null) {
                $this->addFlash('error', 'Ese grupo ya estaba importado.');
            } else {
                $ruleGroup = new RuleGroup();
                $ruleGroup->setName($remote['name'] ?? $uniFiId);
                $ruleGroup->set_id($uniFiId);
                $ruleGroup->setSiteId($remote['site_id'] ?? '');
                $ruleGroup->setGroupType($remote['group_type'] ?? '');
                // Imported but not yet exposed to teachers: an admin should review it
                // (rename, confirm it's meant for network access control) before it
                // shows up as a selectable option on the aula control screen.
                $ruleGroup->setSelectable(false);

                $this->ruleGroupRepository->save($ruleGroup, true);
                $this->addFlash('success', sprintf(
                    'Regla "%s" importada desde UniFi. Revísala y márcala como seleccionable cuando esté lista.',
                    $ruleGroup->getName()
                ));
            }

            return $this->redirectToRoute('admin_rule_group_import');
        }

        $existingIds = array_map(
            static fn (RuleGroup $ruleGroup): string => $ruleGroup->get_id(),
            $this->ruleGroupRepository->findAllOrdered()
        );

        return $this->render('admin/rule_group/import.html.twig', [
            'error' => $error,
            'remote_groups' => $remoteGroups,
            'existing_ids' => $existingIds,
        ]);
    }

    #[Route('/crear-en-unifi', name: 'admin_rule_group_create_remote', methods: ['GET', 'POST'])]
    public function createRemote(Request $request): Response
    {
        $errors = [];

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('create_remote_rule_group', (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }

            $name = trim((string) $request->request->get('name', ''));
            $groupType = trim((string) $request->request->get('group_type', 'address-group'));
            $members = preg_split('/\r\n|\r|\n/', (string) $request->request->get('members', ''));
            $members = array_values(array_filter(array_map('trim', $members), static fn (string $line): bool => $line !== ''));

            if ($name === '') {
                $errors[] = 'El nombre no puede estar vacío.';
            }
            if ($groupType === '') {
                $errors[] = 'El tipo de grupo no puede estar vacío.';
            }

            if ($errors === []) {
                try {
                    $created = $this->uniFiAPIService->createFirewallGroup($name, $groupType, $members);

                    $ruleGroup = new RuleGroup();
                    $ruleGroup->setName($created['name'] ?? $name);
                    $ruleGroup->set_id($created['_id']);
                    $ruleGroup->setSiteId($created['site_id'] ?? '');
                    $ruleGroup->setGroupType($created['group_type'] ?? $groupType);
                    // Same reasoning as importing: created but not yet exposed to teachers
                    // until an admin reviews and marks it selectable.
                    $ruleGroup->setSelectable(false);

                    $this->ruleGroupRepository->save($ruleGroup, true);
                    $this->addFlash('success', sprintf(
                        'Regla "%s" creada en UniFi y registrada aquí. Márcala como seleccionable cuando esté lista.',
                        $ruleGroup->getName()
                    ));

                    return $this->redirectToRoute('admin_rule_group_edit', ['id' => $ruleGroup->getId()]);
                } catch (\RuntimeException $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        return $this->render('admin/rule_group/create_remote.html.twig', [
            'errors' => $errors,
            'name' => (string) $request->request->get('name', ''),
            'group_type' => (string) $request->request->get('group_type', 'address-group'),
            'members' => (string) $request->request->get('members', ''),
        ]);
    }

    #[Route('/{id}/miembros', name: 'admin_rule_group_members', methods: ['GET', 'POST'])]
    public function members(Request $request, RuleGroup $ruleGroup): Response
    {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('rule_group_members', (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }

            $members = preg_split('/\r\n|\r|\n/', (string) $request->request->get('members', ''));
            $members = array_values(array_filter(array_map('trim', $members), static fn (string $line): bool => $line !== ''));

            try {
                $this->uniFiAPIService->setGroupMembers($ruleGroup, $members);
                $this->addFlash('success', sprintf('Miembros de "%s" guardados en UniFi.', $ruleGroup->getName()));
            } catch (\RuntimeException $e) {
                $this->addFlash('error', $e->getMessage());
            }

            return $this->redirectToRoute('admin_rule_group_members', ['id' => $ruleGroup->getId()]);
        }

        $error = null;
        $members = [];

        try {
            $remote = $this->uniFiAPIService->getFirewallGroup($ruleGroup);
            $members = $remote['group_members'] ?? [];
        } catch (\RuntimeException $e) {
            $error = $e->getMessage();
        }

        return $this->render('admin/rule_group/members.html.twig', [
            'ruleGroup' => $ruleGroup,
            'error' => $error,
            'members' => $members,
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

    #[Route('/{id}/eliminar', name: 'admin_rule_group_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, RuleGroup $ruleGroup): Response
    {
        $blockedReason = $this->blockedFromDeletion($ruleGroup);

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('delete_rule_group', (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }

            if ($blockedReason !== null) {
                $this->addFlash('error', $blockedReason);

                return $this->redirectToRoute('admin_rule_group_index');
            }

            if ($request->request->getBoolean('delete_remote')) {
                try {
                    $this->uniFiAPIService->deleteFirewallGroup($ruleGroup);
                } catch (\RuntimeException $e) {
                    $this->addFlash('error', $e->getMessage() . ' No se ha eliminado tampoco aquí, para no perder la referencia.');

                    return $this->redirectToRoute('admin_rule_group_delete', ['id' => $ruleGroup->getId()]);
                }
            }

            $name = $ruleGroup->getName();
            $this->ruleGroupRepository->remove($ruleGroup, true);
            $this->addFlash('success', sprintf('Regla "%s" eliminada.', $name));

            return $this->redirectToRoute('admin_rule_group_index');
        }

        return $this->render('admin/rule_group/delete.html.twig', [
            'ruleGroup' => $ruleGroup,
            'blocked_reason' => $blockedReason,
        ]);
    }

    private function blockedFromDeletion(RuleGroup $ruleGroup): ?string
    {
        if (!$ruleGroup->getNetworks()->isEmpty()) {
            return sprintf('No se puede eliminar "%s": hay aulas que la tienen asignada actualmente.', $ruleGroup->getName());
        }

        if ($this->ruleLogRepository->existsForRuleGroup($ruleGroup)) {
            return sprintf('No se puede eliminar "%s": aparece en el historial de accesos.', $ruleGroup->getName());
        }

        return null;
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
            // No default of true here: an unchecked checkbox simply isn't sent, and a
            // default of true would make it impossible to ever turn this off.
            $selectable = $request->request->getBoolean('selectable');

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
