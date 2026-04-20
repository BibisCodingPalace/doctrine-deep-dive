<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\DataFixtures\AppFixtures;
use App\Entity\User;
use App\Repository\TaskListRepository;
use App\TaskList\SummarizedTaskList;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('stateful')]
class TaskListRepositoryTest extends AbstractRepositoryKernelTestCase
{
    public function testFindListsOwnedByReturnsAllListsForOwner(): void
    {
        $user1 = $this->getUserByEmail(AppFixtures::USER1_EMAIL);
        $repository = $this->getTaskListRepository();

        $lists = $repository->findListsOwnedBy($user1);

        self::assertCount(3, $lists);
        $titles = array_map(static fn ($list) => $list->getTitle(), $lists);
        self::assertContains('User 1 main list', $titles);
        self::assertContains('Shared list of user 1', $titles);
        self::assertContains('Archived list of user 1', $titles);
    }

    public function testFindActiveReturnsOnlyNonArchivedListsForOwner(): void
    {
        $user1 = $this->getUserByEmail(AppFixtures::USER1_EMAIL);
        $repository = $this->getTaskListRepository();

        $lists = $repository->findActive($user1);

        self::assertCount(2, $lists);
        foreach ($lists as $list) {
            self::assertFalse($list->isArchived());
            self::assertSame($user1->getId(), $list->getOwner()->getId());
        }
    }

    public function testFindArchivedReturnsOnlyArchivedListsForOwner(): void
    {
        $user1 = $this->getUserByEmail(AppFixtures::USER1_EMAIL);
        $repository = $this->getTaskListRepository();

        $lists = $repository->findArchived($user1);

        self::assertCount(1, $lists);
        self::assertTrue($lists[0]->isArchived());
        self::assertSame('Archived list of user 1', $lists[0]->getTitle());
    }

    public function testFindListsContributedByReturnsListsWhereUserIsContributor(): void
    {
        $user2 = $this->getUserByEmail(AppFixtures::USER2_EMAIL);
        $repository = $this->getTaskListRepository();

        $lists = $repository->findListsContributedBy($user2);

        self::assertCount(1, $lists);
        self::assertSame('Shared list of user 1', $lists[0]->getTitle());
    }

    public function testFindListsContributedByReturnsEmptyWhenUserHasNoContributions(): void
    {
        $user3 = $this->getUserByEmail(AppFixtures::USER3_EMAIL);
        $repository = $this->getTaskListRepository();

        self::assertSame([], $repository->findListsContributedBy($user3));
    }

    public function testFindSummarizedTaskListForReturnsDtosWithItemCounts(): void
    {
        $user1 = $this->getUserByEmail(AppFixtures::USER1_EMAIL);
        $repository = $this->getTaskListRepository();

        $summaries = $repository->findSummarizedTaskListFor($user1);

        self::assertCount(3, $summaries);

        $byTitle = [];
        foreach ($summaries as $row) {
            self::assertInstanceOf(SummarizedTaskList::class, $row);
            $byTitle[$row->title] = $row;
        }

        self::assertSame(2, $byTitle['User 1 main list']->itemCount);
        self::assertSame(1, $byTitle['Shared list of user 1']->itemCount);
        self::assertSame(1, $byTitle['Archived list of user 1']->itemCount);
        self::assertTrue($byTitle['Archived list of user 1']->archived);
        self::assertFalse($byTitle['User 1 main list']->archived);
    }

    private function getUserByEmail(string $email): User
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        self::assertInstanceOf(User::class, $user, sprintf('Expected user %s to exist after loading fixtures.', $email));

        return $user;
    }

    private function getTaskListRepository(): TaskListRepository
    {
        return self::getContainer()->get(TaskListRepository::class);
    }
}
