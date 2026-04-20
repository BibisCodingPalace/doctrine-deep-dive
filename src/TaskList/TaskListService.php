<?php

declare(strict_types=1);

namespace App\TaskList;

use App\Entity\Task;
use App\Entity\TaskList;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class TaskListService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function createTaskList(User $owner, string $title): TaskList
    {
        $title = trim($title);
        if ($title === '') {
            throw new \InvalidArgumentException('List title cannot be empty.');
        }

        $taskList = new TaskList($owner, $title);
        $this->entityManager->persist($taskList);
        $this->entityManager->flush();

        return $taskList;
    }

    public function addTask(User $actor, TaskList $taskList, string $summary): void
    {
        $summary = trim($summary);
        if ($summary === '') {
            throw new \InvalidArgumentException('Task summary cannot be empty.');
        }

        $this->assertCollaboratorCanEdit($actor, $taskList);

        $taskList->addItem($summary);
        $this->entityManager->flush();
    }

    public function updateTask(User $actor, Task $task): void
    {
        $taskList = $task->getList();
        $this->assertCollaboratorCanEdit($actor, $taskList);

        if ($task->isDone()) {
            $task->reopen();
        } else {
            $task->close();
        }

        $this->entityManager->flush();
    }

    public function addContributor(User $actor, TaskList $taskList, User $contributor): void
    {
        if ($taskList->isArchived()) {
            throw new AccessDeniedException('Archived lists cannot be modified.');
        }

        if (!$this->sameUser($actor, $taskList->getOwner())) {
            throw new AccessDeniedException('Only the list owner can add contributors.');
        }

        if ($this->sameUser($contributor, $taskList->getOwner())) {
            throw new \InvalidArgumentException('The owner is already a member of this list.');
        }

        foreach ($taskList->getContributors() as $existing) {
            if ($this->sameUser($contributor, $existing)) {
                return;
            }
        }

        $taskList->addContributor($contributor);
        $this->entityManager->flush();
    }

    private function assertCollaboratorCanEdit(User $actor, TaskList $taskList): void
    {
        if ($taskList->isArchived()) {
            throw new AccessDeniedException('Archived lists cannot be modified.');
        }

        if ($this->sameUser($actor, $taskList->getOwner())) {
            return;
        }

        foreach ($taskList->getContributors() as $member) {
            if ($this->sameUser($actor, $member)) {
                return;
            }
        }

        throw new AccessDeniedException('You cannot edit this list.');
    }

    private function sameUser(User $a, User $b): bool
    {
        $idA = $a->getId();
        $idB = $b->getId();

        if ($idA !== null && $idB !== null) {
            return $idA === $idB;
        }

        return $a === $b;
    }
}
