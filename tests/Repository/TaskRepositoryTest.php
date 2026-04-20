<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Task;
use App\Repository\TaskRepository;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('stateful')]
class TaskRepositoryTest extends AbstractRepositoryKernelTestCase
{
    public function testFindTasksCreatedTodayReturnsAllFixtureTasks(): void
    {
        $repository = self::getContainer()->get(TaskRepository::class);

        $tasks = $repository->findTasksCreatedToday();

        self::assertCount(6, $tasks);
        foreach ($tasks as $task) {
            self::assertInstanceOf(Task::class, $task);
        }
    }
}
