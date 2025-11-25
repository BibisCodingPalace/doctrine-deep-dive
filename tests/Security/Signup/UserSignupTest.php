<?php declare(strict_types=1);

namespace App\Tests\Security\Signup;

use App\Security\Signup\RequestSignup;
use App\Security\Signup\UserSignup;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Error;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group large
 * @group integration
 */
final class UserSignupTest extends KernelTestCase
{
    /**
     * @group stateful
     */
    public function testCreatesUserFromSignupRequest(): void
    {
        $container = static::getContainer();
        $signup = $container->get(UserSignup::class);
        self::assertInstanceOf(UserSignup::class, $signup);
        $request = new RequestSignup();
        $request->email = 'jane@example.com';
        $request->plainPassword = 'BF628A5B-F3F7-4E4A-8FFE-FE7475768B59';

        $registeredUser = $signup->signup($request);

        self::assertNotNull($registeredUser->getId());
        self::assertSame($request->email, $registeredUser->getUserIdentifier());
        self::assertSame(['ROLE_USER'], $registeredUser->getRoles(), 'User should have ROLE_USER role');
        self::assertNotEmpty($registeredUser->getPassword());
        self::assertNotSame($request->plainPassword, $registeredUser->getPassword(), 'Encoded password should no longer match plain password');
    }

    /**
     * @depends testCreatesUserFromSignupRequest
     */
    public function testFailsWhenEmailIsAlreadyTaken(): void
    {
        $container = static::getContainer();
        $signup = $container->get(UserSignup::class);
        self::assertInstanceOf(UserSignup::class, $signup);
        $request = new RequestSignup();
        $request->email = 'jane@example.com';
        $request->plainPassword = 'BF628A5B-F3F7-4E4A-8FFE-FE7475768B59';

        $this->expectException(UniqueConstraintViolationException::class);
        $this->expectExceptionMessage('duplicate key value violates unique constraint');

        $signup->signup($request);
    }

    public function testFailsWithoutPassword(): void
    {
        $container = static::getContainer();
        $signup = $container->get(UserSignup::class);
        self::assertInstanceOf(UserSignup::class, $signup);
        $request = new RequestSignup();
        $request->email = 'jane@example.com';

        $this->expectException(Error::class);
        $this->expectExceptionMessage('Typed property App\Security\Signup\RequestSignup::$plainPassword must not be accessed before initialization');

        $signup->signup($request);
    }
}
