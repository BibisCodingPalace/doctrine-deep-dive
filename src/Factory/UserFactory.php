<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    public const string DEFAULT_PLAIN_PASSWORD = 'Secret123!';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    #[\Override]
    public static function class(): string
    {
        return User::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'email' => self::faker()->unique()->safeEmail(),
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this->afterInstantiate(function (User $user): void {
            $user->replaceEncodedPassword(
                $this->passwordHasher->hashPassword($user, self::DEFAULT_PLAIN_PASSWORD),
            );
        });
    }
}
