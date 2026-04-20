<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\TaskList;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<TaskList>
 */
final class TaskListFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return TaskList::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'owner' => UserFactory::new(),
            'title' => self::faker()->sentence(3),
        ];
    }

    public function archived(): static
    {
        return $this->afterInstantiate(static function (TaskList $taskList): void {
            $taskList->archive();
        });
    }
}
