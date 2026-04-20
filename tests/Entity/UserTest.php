<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\User;
use App\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Entity behaviour with Zenstruck Foundry; isolation via DAMA (rollback per test).
 */
#[CoversClass(User::class)]
#[Group('entity')]
#[Group('integration')]
#[Group('stateful')]
final class UserTest extends KernelTestCase
{
    public function testFactoryPersistsUserWithValidPasswordHash(): void
    {
        $email = 'foundry-user@example.com';
        $user = UserFactory::createOne(['email' => $email]);

        self::assertNotNull($user->getId());
        self::assertSame($email, $user->getEmail());
        self::assertSame($email, $user->getUserIdentifier());

        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($hasher->isPasswordValid($user, UserFactory::DEFAULT_PLAIN_PASSWORD));
    }

    public function testUserExposesStableRolesAndEmptyCredentialErase(): void
    {
        $user = UserFactory::createOne();

        self::assertSame(['ROLE_USER'], $user->getRoles());

        $user->eraseCredentials();
        self::assertSame(['ROLE_USER'], $user->getRoles());
    }
}
