<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\DataFixtures\AppFixtures;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class AbstractRepositoryKernelTestCase extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->entityManager = self::getContainer()->get('doctrine')->getManager();

        $purger = new ORMPurger($this->entityManager);
        $purger->purge();

        $fixtures = new AppFixtures(self::getContainer()->get(UserPasswordHasherInterface::class));
        $fixtures->load($this->entityManager);

        $this->entityManager->clear();
    }

    protected function tearDown(): void
    {
        if (isset($this->entityManager) && $this->entityManager->isOpen()) {
            $this->entityManager->close();
        }

        self::ensureKernelShutdown();

        parent::tearDown();
    }
}
