<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:teacher:create', description: 'Crea un docente sin permisos de administrador, o retira los de administrador a uno ya existente')]
final class CreateTeacherCommand extends AbstractPersonAccountCommand
{
    protected function isManager(): bool
    {
        return false;
    }

    protected function roleLabel(): string
    {
        return 'docente';
    }
}
