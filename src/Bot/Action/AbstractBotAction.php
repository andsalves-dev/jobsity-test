<?php

namespace App\Bot\Action;

use App\Factory\MessageFactory;
use App\Factory\TransactionFactory;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractBotAction implements BotActionInterface {
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
}