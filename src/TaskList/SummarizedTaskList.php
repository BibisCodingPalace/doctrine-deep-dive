<?php

declare(strict_types=1);

namespace App\TaskList;

use DateTimeImmutable;

/**
 * Read model for SELECT NEW in {@see \App\Repository\TaskListRepository::findSummarizedTaskListFor()}.
 */
final class SummarizedTaskList
{
    public function __construct(
        public int $id,
        public string $name,
        public bool $archived,
        public DateTimeImmutable $created,
        public ?DateTimeImmutable $updated,
        public int $taskCount,
    ) {
    }
}
