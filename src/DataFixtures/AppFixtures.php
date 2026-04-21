<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\TaskList;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public const string USER1_EMAIL = 'user1@example.com';

    public const string USER2_EMAIL = 'user2@example.com';

    public const string USER3_EMAIL = 'user3@example.com';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user1 = new User(self::USER1_EMAIL);
        $user1->replaceEncodedPassword(
            $this->passwordHasher->hashPassword($user1, 'user1'),
        );
        $manager->persist($user1);

        $user1MainList = new TaskList($user1, 'User 1 main list');
        $user1MainList->addItem('First task for user 1');
        $user1MainList->addItem('Second task for user 1');
        $manager->persist($user1MainList);

        $user2 = new User(self::USER2_EMAIL);
        $user2->replaceEncodedPassword(
            $this->passwordHasher->hashPassword($user2, 'user2'),
        );
        $manager->persist($user2);

        $user1SharedList = new TaskList($user1, 'Shared list of user 1');
        $user1SharedList->addItem('Only task on shared list');
        $user1SharedList->addContributor($user2);
        $manager->persist($user1SharedList);

        $user1ArchivedList = new TaskList($user1, 'Archived list of user 1');
        $user1ArchivedList->addItem('Task in archived list');
        $user1ArchivedList->archive();
        $manager->persist($user1ArchivedList);

        $user2List = new TaskList($user2, 'User 2 main list');
        $user2List->addItem('First task for user 2');
        $user2List->addItem('Second task for user 2');
        $manager->persist($user2List);

        $user3 = new User(self::USER3_EMAIL);
        $user3->replaceEncodedPassword(
            $this->passwordHasher->hashPassword($user3, 'user3'),
        );
        $manager->persist($user3);

        $manager->flush();
    }
}
