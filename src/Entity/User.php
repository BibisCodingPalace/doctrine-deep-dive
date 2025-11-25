<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[Entity()]
#[Table(name: 'app_users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[Id()]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column(type: 'bigint', options: ['unsigned' => true])]
    private int|null $id;

    #[Column(type: 'string', unique: true, options: ['length' => 255])]
    private string $email;

    #[Column(name: 'password', type: 'string', options: ['length' => 255])]
    private string $encodedPassword;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function replaceEncodedPassword(string $encodedPassword): void
    {
        $this->encodedPassword = $encodedPassword;
    }

    public function getId(): int|null
    {
        return $this->id;
    }

    public function getPassword(): string|null
    {
        return $this->encodedPassword;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}