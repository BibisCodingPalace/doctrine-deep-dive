<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Factory\TaskFactory;
use App\Factory\TaskListFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Entity behaviour with Zenstruck Foundry; isolation via DAMA (rollback per test).
 */
#[CoversClass(Task::class)]
#[Group('entity')]
#[Group('integration')]
#[Group('stateful')]
final class TaskTest extends KernelTestCase
{
    public function testFactoryPersistsTaskLinkedToList(): void
    {
        $summary = 'Review pull request';
        $task = TaskFactory::createOne(['summary' => $summary]);

        self::assertNotNull($task->getId());
        self::assertSame($summary, $task->getSummary());
        self::assertFalse($task->isDone());
        self::assertInstanceOf(\DateTimeImmutable::class, $task->getCreatedOn());

        $list = $task->getList();
        self::assertNotNull($list->getId());
        self::assertSame($list->getId(), $task->getList()->getId());
    }

    public function testCloseAndReopenToggleDoneFlag(): void
    {
        $list = TaskListFactory::createOne();
        $list->addItem('Write changelog');
        self::getContainer()->get(EntityManagerInterface::class)->flush();

        $task = $list->getItems()[0];
        self::assertFalse($task->isDone());

        $task->close();
        self::assertTrue($task->isDone());

        $task->reopen();
        self::assertFalse($task->isDone());
    }

    public function testCloseWhenAlreadyDoneThrows(): void
    {
        $task = TaskFactory::createOne();
        $task->close();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Task is already done.');
        $task->close();
    }

    public function testReopenWhenAlreadyOpenThrows(): void
    {
        $task = TaskFactory::createOne();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Task is already open.');
        $task->reopen();
    }
}
