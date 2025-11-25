<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity()]
#[Table(name: 'app_task_lists')]
class TaskList
{
    #[Id()]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column(type: 'bigint', options: ['unsigned' => true])]
    private int|null $id;


    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: false)]
    private User $owner;

    #[Column]
    private string $title;

    #[Column(type: 'boolean')]
    private false $archived;

    #[OneToMany(targetEntity: Task::class, mappedBy: "taskList", cascade: ['persist'])]
    private $items;

    #[ManyToMany(targetEntity: User::class)]
    private $contributors;

    #[Column(type: "datetime_immutable")]
    private DateTimeImmutable $created;

    #[Column(type: "datetime_immutable", nullable: true)]
    private DateTimeImmutable $lastUpdated;

    public function __construct(User $owner, string $title)
    {
        $this->owner = $owner;
        $this->title = $title;

        $this->archived = false;
        $this->created = new DateTimeImmutable();
        $this->items = new ArrayCollection();
        $this->contributors = new ArrayCollection();
    }


    public function addItem(string $summary): void
    {
        $this->items->add(new Task($this, $summary));
        $this->lastUpdated = new DateTimeImmutable();
    }

    public function addContributor(User $user): void
    {
        $this->contributors->add($user);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isArchived(): bool
    {
        return $this->archived === true;
    }

    public function getItems(): array
    {
        return $this->items->toArray();
    }

    /**
     * @return User[]
     */
    public function getContributors(): array
    {
        return $this->contributors->toArray();
    }

    public function getCreatedOn(): DateTimeImmutable
    {
        return $this->created;
    }

    public function getLastUpdatedOn(): ?DateTimeImmutable
    {
        return $this->lastUpdated;
    }
}
