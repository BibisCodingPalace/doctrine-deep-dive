<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Task;
use App\Entity\TaskList;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;

#[AsDoctrineListener(event: Events::onFlush)]
final class TaskListLastUpdatedListener
{

    public function __construct(private LoggerInterface $logger)
    {
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $objectManager = $args->getObjectManager();
        if (!$objectManager instanceof EntityManagerInterface) {
            return;
        }

        $unitOfWork = $objectManager->getUnitOfWork();
        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {

            if (!$entity instanceof Task) {
                continue;
            }

            $this->logger->warning('NEW TASK!!!!!'.' '.$entity->getSummary());
        }
    }
}