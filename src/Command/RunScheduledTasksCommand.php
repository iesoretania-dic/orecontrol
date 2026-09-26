<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\NetworkRepository;
use App\Repository\ScheduledTaskRepository;
use App\Service\NetworkRuleGroupService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Lock\LockFactory;

#[AsCommand(name: 'app:scheduled-tasks:run', description: 'Aplica las tareas programadas que correspondan al minuto actual')]
final class RunScheduledTasksCommand extends Command
{
    public function __construct(
        private readonly ScheduledTaskRepository $scheduledTaskRepository,
        private readonly NetworkRepository $networkRepository,
        private readonly NetworkRuleGroupService $ruleGroupService,
        private readonly LockFactory $lockFactory,
        private readonly LoggerInterface $logger,
        #[Autowire(env: 'APP_TIMEZONE')]
        private readonly string $timezone,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lock = $this->lockFactory->createLock('scheduled-tasks-run', ttl: 50);
        if (!$lock->acquire()) {
            $this->logger->info('Scheduled tasks run skipped: already running.');
            $output->writeln('Ya hay una ejecución en curso, se omite.');

            return Command::SUCCESS;
        }

        try {
            $now = new \DateTimeImmutable('now', new \DateTimeZone($this->timezone));
            $weekday = (int) $now->format('N');
            $currentMinuteKey = $now->format('Y-m-d H:i');
            $applied = false;

            foreach ($this->scheduledTaskRepository->findEnabled() as $task) {
                if (!in_array($weekday, $task->getWeekdays(), true)) {
                    continue;
                }
                if ($task->getTime()?->format('H:i') !== $now->format('H:i')) {
                    continue;
                }
                if ($task->getLastRunAt()?->format('Y-m-d H:i') === $currentMinuteKey) {
                    continue;
                }

                $networks = $task->isAllNetworks() ? $this->networkRepository->findAllOrdered() : $task->getNetworks()->toArray();
                foreach ($networks as $network) {
                    $this->ruleGroupService->assign($network, $task->getTargetRuleGroup(), null, null, $task);
                }

                $task->setLastRunAt($now);
                $this->scheduledTaskRepository->save($task, true);
                $applied = true;

                $this->logger->info('Scheduled task applied', [
                    'task' => $task->getName(),
                    'networks' => count($networks),
                ]);
                $output->writeln(sprintf('Aplicada la tarea "%s" (%d aulas).', $task->getName(), count($networks)));
            }

            if ($applied) {
                $this->ruleGroupService->pushToUniFi();
            }
        } finally {
            $lock->release();
        }

        return Command::SUCCESS;
    }
}
