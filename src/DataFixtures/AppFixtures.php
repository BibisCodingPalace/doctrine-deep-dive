<?php

namespace App\DataFixtures;

use App\Entity\TaskList;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user1 = new User('user2@example.com');
        $user1->replaceEncodedPassword(
            $this->passwordHasher->hashPassword($user1, 'user1'),
        );
        $manager->persist($user1);

        $user1List = new TaskList($user1, 'Liste von user1');
        $user1List->addItem('Ersten Task für user1 anlegen');
        $user1List->addItem('Zweiten Task für user1 anlegen');
        $manager->persist($user1List);

        $user2 = new User('user2@example.com');
        $user2->replaceEncodedPassword(
            $this->passwordHasher->hashPassword($user2, 'user2'),
        );
        $manager->persist($user2);

        $user2List = new TaskList($user2, 'Liste von user2');
        $user2List->addItem('Ersten Task für user2 anlegen');
        $user2List->addItem('Zweiten Task für user2 anlegen');
        $manager->persist($user2List);

        $manager->flush();
    }
}
