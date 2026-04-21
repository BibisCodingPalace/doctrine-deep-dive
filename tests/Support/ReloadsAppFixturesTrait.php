<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\DataFixtures\AppFixtures;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Purges the default entity manager and loads {@see AppFixtures}.
 *
 * Use only from test classes that extend {@see \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase}.
 */
trait ReloadsAppFixturesTrait
{
    protected static function reloadAppFixturesIntoDatabase(): void
    {
        self::bootKernel();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get('doctrine')->getManager();

        $purger = new ORMPurger($em);
        $purger->purge();

        (new AppFixtures(static::getContainer()->get(UserPasswordHasherInterface::class)))->load($em);
        $em->clear();

        static::ensureKernelShutdown();
    }
}
