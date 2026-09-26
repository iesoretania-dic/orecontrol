<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Person;
use App\Entity\ScheduledTask;
use App\Repository\NetworkRepository;
use App\Repository\RuleGroupRepository;
use App\Repository\ScheduledTaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/tareas-programadas')]
#[IsGranted('ROLE_ADMIN')]
class ScheduledTaskController extends AbstractController
{
    private const WEEKDAY_LABELS = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo',
    ];

    public function __construct(
        private readonly ScheduledTaskRepository $scheduledTaskRepository,
        private readonly NetworkRepository $networkRepository,
        private readonly RuleGroupRepository $ruleGroupRepository,
    ) {
    }

    #[Route('', name: 'admin_scheduled_task_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/scheduled_task/index.html.twig', [
            'tasks' => $this->scheduledTaskRepository->findAllOrdered(),
            'weekday_labels' => self::WEEKDAY_LABELS,
        ]);
    }

    #[Route('/nueva', name: 'admin_scheduled_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->handleForm($request, new ScheduledTask());
    }

    #[Route('/{id}/editar', name: 'admin_scheduled_task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ScheduledTask $scheduledTask): Response
    {
        return $this->handleForm($request, $scheduledTask);
    }

    #[IsCsrfTokenValid('delete_scheduled_task', tokenKey: '_token')]
    #[Route('/{id}/eliminar', name: 'admin_scheduled_task_delete', methods: ['POST'])]
    public function delete(ScheduledTask $scheduledTask): Response
    {
        $this->scheduledTaskRepository->remove($scheduledTask, true);
        $this->addFlash('success', sprintf('Tarea "%s" eliminada.', $scheduledTask->getName()));

        return $this->redirectToRoute('admin_scheduled_task_index');
    }

    #[IsCsrfTokenValid('toggle_scheduled_task', tokenKey: '_token')]
    #[Route('/{id}/activar', name: 'admin_scheduled_task_activate', methods: ['POST'])]
    public function activate(ScheduledTask $scheduledTask): Response
    {
        $scheduledTask->setEnabled(true);
        $this->scheduledTaskRepository->save($scheduledTask, true);

        return $this->redirectToRoute('admin_scheduled_task_index');
    }

    #[IsCsrfTokenValid('toggle_scheduled_task', tokenKey: '_token')]
    #[Route('/{id}/desactivar', name: 'admin_scheduled_task_deactivate', methods: ['POST'])]
    public function deactivate(ScheduledTask $scheduledTask): Response
    {
        $scheduledTask->setEnabled(false);
        $this->scheduledTaskRepository->save($scheduledTask, true);

        return $this->redirectToRoute('admin_scheduled_task_index');
    }

    private function handleForm(Request $request, ScheduledTask $task): Response
    {
        $isNew = $task->getId() === null;
        $errors = [];

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('scheduled_task_form', (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }

            $name = trim((string) $request->request->get('name', ''));
            // No default of true here: an unchecked checkbox simply isn't sent, and a
            // default of true would make it impossible to ever turn this off.
            $enabled = $request->request->getBoolean('enabled');
            $allNetworks = $request->request->getBoolean('all_networks');
            $networkIds = $request->request->all('networks');
            $ruleGroupId = $request->request->get('target_rule_group');
            $weekdays = array_map('intval', $request->request->all('weekdays'));
            $timeValue = (string) $request->request->get('time', '');

            if ($name === '') {
                $errors[] = 'El nombre no puede estar vacío.';
            }
            if ($weekdays === []) {
                $errors[] = 'Seleccione al menos un día de la semana.';
            }
            if (!$allNetworks && $networkIds === []) {
                $errors[] = 'Seleccione al menos un aula, o marque "todas las aulas".';
            }
            $time = \DateTimeImmutable::createFromFormat('H:i', $timeValue) ?: null;
            if ($time === null) {
                $errors[] = 'Indique una hora válida.';
            }

            if ($errors === []) {
                $task->setName($name);
                $task->setEnabled($enabled);
                $task->setAllNetworks($allNetworks);
                $task->setWeekdays($weekdays);
                $task->setTime($time);
                $task->setTargetRuleGroup($ruleGroupId !== null && $ruleGroupId !== '' ? $this->ruleGroupRepository->find($ruleGroupId) : null);

                $task->clearNetworks();
                if (!$allNetworks) {
                    foreach ($this->networkRepository->findBy(['id' => $networkIds]) as $network) {
                        $task->addNetwork($network);
                    }
                }

                if ($isNew) {
                    $user = $this->getUser();
                    $task->setCreatedBy($user instanceof Person ? $user : null);
                }

                $this->scheduledTaskRepository->save($task, true);
                $this->addFlash('success', sprintf('Tarea "%s" guardada.', $task->getName()));

                return $this->redirectToRoute('admin_scheduled_task_index');
            }
        }

        return $this->render('admin/scheduled_task/form.html.twig', [
            'task' => $task,
            'is_new' => $isNew,
            'errors' => $errors,
            'networks' => $this->networkRepository->findAllOrdered(),
            'rule_groups' => $this->ruleGroupRepository->findAllSelectable(),
            'weekday_labels' => self::WEEKDAY_LABELS,
        ]);
    }
}
