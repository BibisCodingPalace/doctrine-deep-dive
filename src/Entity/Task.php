<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\Persistence\PropertyChangedListener;

#[Entity()]
#[Table(name: 'app_task_items')]
class Task
{
    private $listeners = [];

    #[Id()]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column(type: 'bigint', options: ['unsigned' => true])]
    private int|null $id;

    #[ManyToOne(targetEntity: TaskList::class, inversedBy: "items")]
    private $taskList;

    #[Column(type: 'string', options: ['length' => 255])]
    private string $summary;

    #[Column(type: 'boolean')]
    private bool $done;

    #[Column(type: 'datetime_immutable')]
    private $created;

    /**
     * @internal New TaskItems should only be generated through TaskList::addItem()
     */
    public function __construct(TaskList $taskList, string $summary)
    {
        $this->taskList = $taskList;
        $this->summary = $summary;

        $this->done = false;
        $this->created = new DateTimeImmutable();
    }

    public function reopen(): void
    {
        if ($this->done === false) {
            throw new \RuntimeException('Task is already open.');
        }

        //$this->notifyListeners('done', true, false);
        $this->done = false;
    }

    public function close(): void
    {
        if ($this->done === true) {
            throw new \RuntimeException('Task is already done.');
        }

        $this->notifyListeners('done', false, true);
        $this->done = true;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getList(): TaskList
    {
        return $this->taskList;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function isDone(): bool
    {
        return $this->done;
    }

    public function getCreatedOn(): DateTimeImmutable
    {
        return $this->created;
    }

    private function notifyListeners(string $propertyName, $oldValue, $newValue): void
    {
        foreach ($this->listeners as $listener) {
            $listener->propertyChanged($this, $propertyName, $oldValue, $newValue);
        }
    }

    public function addPropertyChangedListener(PropertyChangedListener $listener): void
    {
        $this->listeners[] = $listener;
    }
}
