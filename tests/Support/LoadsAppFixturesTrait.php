<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\DataFixtures\AppFixtures;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Loads {@see \App\DataFixtures\AppFixtures} into the current {@see EntityManagerInterface} instance.
 *
 * With dama/doctrine-test-bundle, each test runs inside a transaction that is rolled back
 * at the end—the database is empty again (the schema remains). Purge + load in {@see setUp}
 * therefore gives every test the same starting point and still works if PHPUnit runs
 * without the DAMA extension.
 *
 * Requires the kernel to be booted already ({@see KernelTestCase::bootKernel()} or
 * {@see \Symfony\Bundle\FrameworkBundle\Test\WebTestCase::createClient()}).
 *
 * @mixin KernelTestCase
 */
trait LoadsAppFixturesTrait
{
    protected static function loadAppFixtures(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $purger = new ORMPurger($entityManager);
        $purger->purge();

        $fixtures = new AppFixtures(
            static::getContainer()->get(UserPasswordHasherInterface::class),
        );
        $fixtures->load($entityManager);
        $entityManager->clear();
    }
}
