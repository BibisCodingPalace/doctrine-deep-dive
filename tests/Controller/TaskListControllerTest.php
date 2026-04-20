<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('smoke')]
class TaskListControllerTest extends WebTestCase
{
    public function testListIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'My Todo List');
        self::assertSelectorTextContains('button', 'Create a list');
        self::assertSelectorTextContains('a.active', 'All Lists');
    }
}
