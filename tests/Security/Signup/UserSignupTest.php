<?php

declare(strict_types=1);

namespace App\Tests\Security\Signup;

use App\Security\Signup\RequestSignup;
use App\Security\Signup\UserSignup;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Error;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Kernel integration; database access runs inside the DAMA test transaction.
 */
#[Group('integration')]
#[Group('stateful')]
final class UserSignupTest extends KernelTestCase
{
    public function testCreatesUserAndSecondSignupWithSameEmailHitsUniqueConstraint(): void
    {
        $container = static::getContainer();
        $signup = $container->get(UserSignup::class);
        self::assertInstanceOf(UserSignup::class, $signup);

        $email = sprintf('jane.%s@example.com', bin2hex(random_bytes(8)));

        $request = new RequestSignup();
        $request->email = $email;
        $request->plainPassword = 'BF628A5B-F3F7-4E4A-8FFE-FE7475768B59';

        $registeredUser = $signup->signup($request);

        self::assertNotNull($registeredUser->getId());
        self::assertSame($request->email, $registeredUser->getUserIdentifier());
        self::assertSame(['ROLE_USER'], $registeredUser->getRoles(), 'User should have ROLE_USER role');
        self::assertNotEmpty($registeredUser->getPassword());
        self::assertNotSame($request->plainPassword, $registeredUser->getPassword(), 'Encoded password should no longer match plain password');

        $duplicateRequest = new RequestSignup();
        $duplicateRequest->email = $email;
        $duplicateRequest->plainPassword = 'BF628A5B-F3F7-4E4A-8FFE-FE7475768B59';

        try {
            $signup->signup($duplicateRequest);
            self::fail('Expected unique constraint violation when registering the same email twice.');
        } catch (UniqueConstraintViolationException $e) {
            self::assertMatchesRegularExpression(
                '/duplicate|unique constraint|UNIQUE constraint failed/i',
                $e->getMessage(),
            );
        }
    }

    public function testFailsWithoutPassword(): void
    {
        $container = static::getContainer();
        $signup = $container->get(UserSignup::class);
        self::assertInstanceOf(UserSignup::class, $signup);
        $request = new RequestSignup();
        $request->email = sprintf('nobody.%s@example.com', bin2hex(random_bytes(4)));

        $this->expectException(Error::class);
        $this->expectExceptionMessage('Typed property App\Security\Signup\RequestSignup::$plainPassword must not be accessed before initialization');

        $signup->signup($request);
    }
}
