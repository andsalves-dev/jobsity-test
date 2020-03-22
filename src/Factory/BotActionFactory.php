<?php

namespace App\Factory;

use App\Bot\Action\BotActionInterface;
use Doctrine\ORM\EntityManagerInterface;

class BotActionFactory {
    /** @var EntityManagerInterface */
    protected $entityManager;
    /** @var MessageFactory */
    protected $messageFactory;
    /** @var TransactionFactory */
    protected $transactionFactory;

    public function __construct(
        EntityManagerInterface $entityManager,
        MessageFactory $messageFactory,
        TransactionFactory $transactionFactory
    ) {
        $this->entityManager = $entityManager;
        $this->messageFactory = $messageFactory;
        $this->transactionFactory = $transactionFactory;
    }

    public function create(string $botActionClass): BotActionInterface {
        return new $botActionClass(
            $this->entityManager,
            $this->messageFactory,
            $this->transactionFactory
        );
    }
}