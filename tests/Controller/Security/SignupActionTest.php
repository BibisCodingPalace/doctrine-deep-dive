<?php declare(strict_types=1);

namespace App\Tests\Controller\Security;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('acceptance')]
class SignupActionTest extends WebTestCase
{
    public function testSignupPageShowsForm(): void
    {
        $client = static::createClient();
        $client->request('GET', '/signup');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('form', 'Email address');
        self::assertSelectorTextContains('form', 'Password');
        self::assertSelectorTextContains('form', 'Repeat password');
    }

    #[Group('stateful')]
    public function testPassesWithValidEmailAndSecurePassword(): void
    {
        $email = sprintf('jane.doe+%s@example.com', bin2hex(random_bytes(4)));

        $client = static::createClient();
        $client->request('GET', '/signup');

        $client->submitForm('Sign up', [
            'request_signup_form[email]' => $email,
            'request_signup_form[plainPassword][first]' => '4B30596E-DE78-49B7-8809-B07519612DC4',
            'request_signup_form[plainPassword][second]' => '4B30596E-DE78-49B7-8809-B07519612DC4',
        ]);

        self::assertResponseRedirects('/login');
    }

    public function testFailsWithInsecurePassword(): void
    {
        $client = static::createClient();
        $client->request('GET', '/signup');

        $client->submitForm('Sign up', [
            'request_signup_form[email]' => 'weak-password@example.com',
            'request_signup_form[plainPassword][first]' => 'test',
            'request_signup_form[plainPassword][second]' => 'test',
        ]);
        $response = $client->getResponse();

        self::assertSame(422, $response->getStatusCode());
    }
}
