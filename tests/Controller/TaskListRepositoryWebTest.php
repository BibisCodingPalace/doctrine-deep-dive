<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\User;
use App\Repository\TaskListRepository;
use App\Tests\AbstractWebFixtureTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('stateful')]
final class TaskListRepositoryWebTest extends AbstractWebFixtureTestCase
{
    public function testIndexWithOwnFilterShowsFixtureLists(): void
    {
        $client = static::getClient();
        $client->loginUser($this->fixtureUser(AppFixtures::USER1_EMAIL));
        $client->request('GET', '/?filter=own');

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('User 1 Main List', $content);
        self::assertStringContainsString('list with 2 tasks', $content);
    }

    public function testContributingFilterShowsSharedList(): void
    {
        $client = static::getClient();
        $client->loginUser($this->fixtureUser(AppFixtures::USER2_EMAIL));
        $client->request('GET', '/?filter=contributing');

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Shared List Of User 1', $content);
        self::assertStringContainsString('list with 1 tasks', $content);
    }

    public function testShowRendersListFromDatabase(): void
    {
        $client = static::getClient();
        $user = $this->fixtureUser(AppFixtures::USER1_EMAIL);
        $client->loginUser($user);
        $listId = $this->ownedListIdByTitle($user, 'User 1 main list');

        $client->request('GET', '/show/'.$listId);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('User 1 Main List', (string) $client->getResponse()->getContent());
    }

    public function testRecentPageShowsTodaysTasksForList(): void
    {
        $client = static::getClient();
        $user = $this->fixtureUser(AppFixtures::USER1_EMAIL);
        $client->loginUser($user);
        $listId = $this->ownedListIdByTitle($user, 'User 1 main list');

        $client->request('GET', '/recent/'.$listId);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('First Task For User 1', (string) $client->getResponse()->getContent());
    }

    public function testShowRedirectsGuestsToLogin(): void
    {
        $client = static::getClient();
        $listId = $this->ownedListIdByTitle(
            $this->fixtureUser(AppFixtures::USER1_EMAIL),
            'User 1 main list',
        );

        $client->request('GET', '/show/'.$listId);

        self::assertResponseRedirects('/login');
    }

    private function fixtureUser(string $email): User
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        self::assertInstanceOf(User::class, $user);

        return $user;
    }

    private function ownedListIdByTitle(User $user, string $title): int
    {
        $repo = static::getContainer()->get(TaskListRepository::class);
        foreach ($repo->findListsOwnedBy($user) as $list) {
            if ($list->getTitle() === $title) {
                return $list->getId();
            }
        }

        self::fail(sprintf('No owned list with title "%s" found.', $title));
    }
}
