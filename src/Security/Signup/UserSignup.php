<?php declare(strict_types=1);

namespace App\Security\Signup;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class UserSignup
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function signup(
        RequestSignup $signupRequest
    ): User {
        $user = new User($signupRequest->email);
        $user->replaceEncodedPassword($this->passwordHasher->hashPassword($user, $signupRequest->plainPassword));

        $entityManager = $this->managerRegistry->getManagerForClass(User::class);

        $entityManager->persist($user);
        $entityManager->flush();
        $entityManager->clear();

        return $user;
    }
}