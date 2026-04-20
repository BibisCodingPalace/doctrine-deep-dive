<?php

namespace App\DataFixtures;

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
        $user1 = new User('user1@symfony.com');
        $user1->replaceEncodedPassword(
            $this->passwordHasher->hashPassword($user1, 'user1'),
        );
        $manager->persist($user1);

        $user2 = new User('user2@symfony.com');
        $user2->replaceEncodedPassword(
            $this->passwordHasher->hashPassword($user2, 'user2'),
        );
        $manager->persist($user2);

        $manager->flush();
    }
}
