<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Tests\Support\LoadsAppFixturesTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Repository integration tests backed by {@see App\DataFixtures\AppFixtures}.
 *
 * Per test: boot the kernel and load fixtures (inside the DAMA test transaction).
 */
abstract class AbstractRepositoryKernelTestCase extends KernelTestCase
{
    use LoadsAppFixturesTrait;

    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
        static::loadAppFixtures();
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
