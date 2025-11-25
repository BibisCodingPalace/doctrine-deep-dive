<?php declare(strict_types=1);

namespace App\Tests\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group large
 * @group acceptance
 */
class SignupActionTest extends WebTestCase
{
    public function testSignupPageShowsForm(): void
    {
        $client = static::createClient();
        $client->request('GET', '/signup');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'User signup');
        self::assertSelectorTextContains('form', 'Email');
        self::assertSelectorTextContains('form', 'Password');
        self::assertSelectorTextContains('form', 'Repeat password');
    }

    /**
     * @group stateful
     */
    public function testPassesWithValidEmailAndSecurePassword(): void
    {
        $client = static::createClient();
        $client->request('GET', '/signup');

        $crawler = $client->submitForm('Sign up', [
            'request_signup_form[email]' => 'jane.doe@example.com',
            'request_signup_form[plainPassword][first]' => '4B30596E-DE78-49B7-8809-B07519612DC4',
            'request_signup_form[plainPassword][second]' => '4B30596E-DE78-49B7-8809-B07519612DC4',
        ]);
        $response = $client->getResponse();

        self::assertTrue($response->isRedirect('/login'), $crawler->filter('title')->text());
    }

    public function testFailsWithInsecurePassword(): void
    {
        $client = static::createClient();
        $client->request('GET', '/signup');

        $crawler = $client->submitForm('Sign up', [
            'request_signup_form[email]' => 'jane.doe@example.com',
            'request_signup_form[plainPassword][first]' => 'test',
            'request_signup_form[plainPassword][second]' => 'test',
        ]);
        $response = $client->getResponse();

        self::assertSame(422, $response->getStatusCode());
    }
}
