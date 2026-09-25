<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Person;
use App\Repository\PersonRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Shared logic for creating or updating a Person from the console, as either
 * an administrator (CreateAdminCommand) or a plain teacher (CreateTeacherCommand).
 */
abstract class AbstractPersonAccountCommand extends Command
{
    public function __construct(
        private readonly PersonRepository $personRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    abstract protected function isManager(): bool;

    abstract protected function roleLabel(): string;

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'Usuario del docente')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Contraseña local (se pedirá de forma interactiva si se omite)')
            ->addOption('external', null, InputOption::VALUE_NONE, 'Autenticar contra iSéneca en vez de con contraseña local')
            ->addOption('level', null, InputOption::VALUE_REQUIRED, 'Nivel del docente', '0')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = trim((string) $input->getArgument('username'));
        if ($username === '') {
            $io->error('El usuario no puede estar vacío.');

            return Command::FAILURE;
        }

        $external = (bool) $input->getOption('external');

        $person = $this->personRepository->findOneBy(['username' => $username]);
        $isNew = $person === null;
        if ($isNew) {
            $person = new Person();
            $person->setUsername($username);
            $person->setLevel((int) $input->getOption('level'));
        }

        $person->setManager($this->isManager());
        $person->setActive(true);
        $person->setExternal($external);

        if ($external) {
            $person->setPassword(null);
        } else {
            $password = $input->getOption('password');
            if ($password === null) {
                $question = new Question('Contraseña: ');
                $question->setHidden(true);
                $question->setHiddenFallback(false);
                $password = $io->askQuestion($question);
            }

            if (!is_string($password) || $password === '') {
                $io->error('La contraseña no puede estar vacía para un docente local.');

                return Command::FAILURE;
            }

            $person->setPassword($this->passwordHasher->hashPassword($person, $password));
        }

        $this->personRepository->save($person, true);

        $io->success(sprintf(
            '"%s" %s como %s (%s).',
            $username,
            $isNew ? 'creado' : 'actualizado',
            $this->roleLabel(),
            $external ? 'iSéneca' : 'contraseña local'
        ));

        return Command::SUCCESS;
    }
}
