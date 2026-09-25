<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:admin:create', description: 'Crea un docente administrador, o promueve a administrador uno ya existente')]
final class CreateAdminCommand extends AbstractPersonAccountCommand
{
    protected function isManager(): bool
    {
        return true;
    }

    protected function roleLabel(): string
    {
        return 'administrador';
    }
}
