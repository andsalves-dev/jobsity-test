<?php

namespace App\Service;

use App\Bot\Action\BotActionInterface;
use App\Bot\Action\CheckBalanceAction;
use App\Bot\Action\TransactionAction;
use App\Bot\Action\SalutationAction;
use App\Factory\BotActionFactory;

class MessageInterpreter {
    /** @var BotActionFactory */
    private $botActionFactory;

    /** @var BotActionInterface[] */
    public $availableActions = [
        TransactionAction::class,
        SalutationAction::class,
        CheckBalanceAction::class,
    ];

    public function __construct(BotActionFactory $botActionFactory) {
        $this->botActionFactory = $botActionFactory;
    }

    public function findActionRunner(string $text): ?BotActionInterface {
        foreach ($this->availableActions as $availableActionClass) {
            if ($availableActionClass::isValid($text)) {
                return $this->botActionFactory->create($availableActionClass);
            }
        }

        return null;
    }
}