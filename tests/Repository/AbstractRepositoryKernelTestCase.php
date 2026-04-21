<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Tests\Support\ReloadsAppFixturesTrait;
use App\Tests\Support\ReloadsAppFixturesTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractRepositoryKernelTestCase extends KernelTestCase
{
    use ReloadsAppFixturesTrait;

    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        static::reloadAppFixturesIntoDatabase();

        self::bootKernel();
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
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
