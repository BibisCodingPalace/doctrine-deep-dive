<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Task;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Task>
 */
final class TaskFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Task::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'taskList' => TaskListFactory::new(),
            'summary' => self::faker()->sentence(4),
        ];
    }
}
