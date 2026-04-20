<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\TaskList;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::onFlush)]
final class TaskListLastUpdatedListener
{
    public function onFlush(OnFlushEventArgs $args): void
    {
        $objectManager = $args->getObjectManager();
        if (!$objectManager instanceof EntityManagerInterface) {
            return;
        }

        $unitOfWork = $objectManager->getUnitOfWork();
        $class = $objectManager->getClassMetadata(TaskList::class);

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if (!$entity instanceof TaskList) {
                continue;
            }

            $changeSet = $unitOfWork->getEntityChangeSet($entity);
            unset($changeSet['lastUpdated']);

            if ($changeSet === []) {
                continue;
            }

            $entity->touchLastUpdated();
            $unitOfWork->recomputeSingleEntityChangeSet($class, $entity);
        }
    }
}
