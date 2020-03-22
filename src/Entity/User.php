<?php

namespace App\Entity;

use App\Util\PasswordEncoder;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $defaultCurrency;

    public function getId(): ?int {
        return $this->id;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(string $name): self {
        $this->name = $name;

        return $this;
    }

    public function getUsername(): ?string {
        return $this->username;
    }

    public function setUsername(string $username): self {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(string $email): self {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string {
        return $this->password;
    }

    public function setPassword(string $password): self {
        $this->password = $password;

        return $this;
    }

    public function getDefaultCurrency(): ?string {
        return $this->defaultCurrency;
    }

    public function setDefaultCurrency(string $defaultCurrency): self {
        $this->defaultCurrency = $defaultCurrency;

        return $this;
    }

    public function getClientArrayCopy() {
        return [
            'name' => $this->getName(),
            'username' => $this->getUsername(),
            'email' => $this->getEmail(),
            'default_currency' => $this->getDefaultCurrency(),
        ];
    }

    public function getRoles() {
        return [];
    }

    public function getSalt() {
        return PasswordEncoder::$defaultSalt;
    }

    public function eraseCredentials() {
        return;
    }
}
