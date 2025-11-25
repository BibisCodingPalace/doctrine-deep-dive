<?php declare(strict_types=1);

namespace App\Tests\Security\Signup;

use App\Security\Signup\RequestSignup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @group medium
 * @group unit
 */
final class RequestSignupTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testValidationPassesWithSecurePassword(): void
    {
        $request = new RequestSignup();
        $request->email = 'jane@example.com';
        $request->plainPassword = 'BF628A5B-F3F7-4E4A-8FFE-FE7475768B59';

        $errors = $this->validator->validate($request);

        $this->assertCount(0, $errors, (string) $errors);
    }

    public function testValidationFailsWithInsecurePassword(): void
    {
        $request = new RequestSignup();
        $request->email = 'jane@example.com';
        $request->plainPassword = 'test';

        $errors = $this->validator->validate($request);

        $this->assertCount(1, $errors, (string) $errors);
        $this->assertSame('The password strength is too low. Please use a stronger password.', $errors[0]->getMessage());
    }

    public function testValidationFailsWithInvalidEmailAddress(): void
    {
        $request = new RequestSignup();
        $request->email = 'jane@local';
        $request->plainPassword = 'BF628A5B-F3F7-4E4A-8FFE-FE7475768B59';

        $errors = $this->validator->validate($request);

        $this->assertCount(1, $errors, (string) $errors);
        $this->assertSame('This value is not a valid email address.', $errors[0]->getMessage());
    }

    public function testValidationFailsWithoutData(): void
    {
        $request = new RequestSignup();

        $errors = $this->validator->validate($request);

        $this->assertCount(2, $errors, (string) $errors);
        $this->assertSame('This value should not be blank.', $errors[0]->getMessage());
        $this->assertSame('This value should not be blank.', $errors[1]->getMessage());
    }
}
