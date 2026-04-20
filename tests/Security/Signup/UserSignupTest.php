<?php declare(strict_types=1);

namespace App\Tests\Security\Signup;

use App\Security\Signup\RequestSignup;
use App\Security\Signup\UserSignup;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Error;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[Group('integration')]
final class UserSignupTest extends KernelTestCase
{
    private static string $signupTestEmail;

    public static function setUpBeforeClass(): void
    {
        self::$signupTestEmail = sprintf('jane.%s@example.com', bin2hex(random_bytes(8)));
    }

    #[Group('stateful')]
    public function testCreatesUserFromSignupRequest(): string
    {
        $container = static::getContainer();
        $signup = $container->get(UserSignup::class);
        self::assertInstanceOf(UserSignup::class, $signup);
        $request = new RequestSignup();
        $request->email = self::$signupTestEmail;
        $request->plainPassword = 'BF628A5B-F3F7-4E4A-8FFE-FE7475768B59';

        $registeredUser = $signup->signup($request);

        self::assertNotNull($registeredUser->getId());
        self::assertSame($request->email, $registeredUser->getUserIdentifier());
        self::assertSame(['ROLE_USER'], $registeredUser->getRoles(), 'User should have ROLE_USER role');
        self::assertNotEmpty($registeredUser->getPassword());
        self::assertNotSame($request->plainPassword, $registeredUser->getPassword(), 'Encoded password should no longer match plain password');

        return $registeredUser->getUserIdentifier();
    }

    #[Depends('testCreatesUserFromSignupRequest')]
    public function testFailsWhenEmailIsAlreadyTaken(string $email): void
    {
        $container = static::getContainer();
        $signup = $container->get(UserSignup::class);
        self::assertInstanceOf(UserSignup::class, $signup);
        $request = new RequestSignup();
        $request->email = $email;
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
        $request->email = sprintf('nobody.%s@example.com', bin2hex(random_bytes(4)));

        $this->expectException(Error::class);
        $this->expectExceptionMessage('Typed property App\Security\Signup\RequestSignup::$plainPassword must not be accessed before initialization');

        $signup->signup($request);
    }
}
