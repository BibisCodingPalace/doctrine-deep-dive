<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\TaskList;
use App\Factory\TaskListFactory;
use App\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Entity behaviour with Zenstruck Foundry; isolation via DAMA (rollback per test).
 */
#[CoversClass(TaskList::class)]
#[Group('entity')]
#[Group('integration')]
#[Group('stateful')]
final class TaskListTest extends KernelTestCase
{
    public function testFactoryPersistsListWithOwnerTitleAndCreatedTimestamp(): void
    {
        $owner = UserFactory::createOne();
        $title = 'Weekly groceries';

        $list = TaskListFactory::createOne([
            'owner' => $owner,
            'title' => $title,
        ]);

        self::assertNotNull($list->getId());
        self::assertSame($owner->getId(), $list->getOwner()->getId());
        self::assertSame($title, $list->getTitle());
        self::assertFalse($list->isArchived());
        self::assertInstanceOf(\DateTimeImmutable::class, $list->getCreatedOn());
    }

    public function testAddItemCreatesTaskAndUpdatesLastUpdated(): void
    {
        $list = TaskListFactory::createOne();
        self::assertNull($list->getLastUpdatedOn());

        $list->addItem('Pick up parcel');
        self::getContainer()->get(EntityManagerInterface::class)->flush();

        $items = $list->getItems();
        self::assertCount(1, $items);
        self::assertSame('Pick up parcel', $items[0]->getSummary());
        self::assertFalse($items[0]->isDone());
        self::assertNotNull($list->getLastUpdatedOn());
    }

    public function testContributorAssociationIsPersisted(): void
    {
        $owner = UserFactory::createOne();
        $contributor = UserFactory::createOne();

        $list = TaskListFactory::createOne(['owner' => $owner]);
        $list->addContributor($contributor);
        self::getContainer()->get(EntityManagerInterface::class)->flush();
        self::getContainer()->get(EntityManagerInterface::class)->clear();

        $reloaded = self::getContainer()->get(EntityManagerInterface::class)->find(TaskList::class, $list->getId());
        self::assertInstanceOf(TaskList::class, $reloaded);
        $contributorIds = array_map(static fn ($u) => $u->getId(), $reloaded->getContributors());
        self::assertContains($contributor->getId(), $contributorIds);
    }

    public function testArchivedStateFactory(): void
    {
        $list = TaskListFactory::new()->archived()->create();

        self::assertTrue($list->isArchived());
    }
}
