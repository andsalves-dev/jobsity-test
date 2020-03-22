<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TransactionRepository")
 */
class Transaction {
    const DEPOSIT_TYPE = 'deposit';
    const WITHDRAWAL_TYPE = 'withdrawal';

    const TYPES = [self::DEPOSIT_TYPE, self::WITHDRAWAL_TYPE];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $type;

    /**
     * @ORM\Column(type="float")
     */
    private $balanceBefore;

    /**
     * @ORM\Column(type="float")
     */
    private $balanceAfter;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $amountCurrency;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function getId(): ?int {
        return $this->id;
    }

    public function getType(): ?string {
        return $this->type;
    }

    public function setType(string $type): self {
        $this->type = $type;

        return $this;
    }

    public function getAmountCurrency(): ?string {
        return $this->amountCurrency;
    }

    public function setAmountCurrency(string $amountCurrency): self {
        $this->amountCurrency = $amountCurrency;

        return $this;
    }

    public function getBalanceBefore(): ?float {
        return $this->balanceBefore;
    }

    public function setBalanceBefore(float $balanceBefore): self {
        $this->balanceBefore = $balanceBefore;

        return $this;
    }

    public function getBalanceAfter(): ?float {
        return $this->balanceAfter;
    }

    public function setBalanceAfter(float $balanceAfter): self {
        $this->balanceAfter = $balanceAfter;

        return $this;
    }

    public function getAmount(): ?float {
        return $this->amount;
    }

    public function setAmount(float $amount): self {
        $this->amount = $amount;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self {
        $this->date = $date;

        return $this;
    }

    public function getUser(): ?User {
        return $this->user;
    }

    public function setUser(?User $user): self {
        $this->user = $user;

        return $this;
    }
}
