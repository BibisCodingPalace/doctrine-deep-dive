<?php

namespace App\Repository;

use App\Entity\Task;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findTasksCreatedToday()
    {
        $dql = <<<DQL
SELECT task
FROM App\Entity\Task task
WHERE task.created BETWEEN :startdate AND :enddate
DQL;

        return $this->getEntityManager()->createQuery($dql)
            ->setParameter('startdate', new DateTimeImmutable('today'))
            ->setParameter('enddate', new DateTimeImmutable())
            ->getResult();
    }
}