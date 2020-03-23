<?php

namespace App\Bot\Action;

use App\Entity\Message;
use App\Entity\User;

interface BotActionInterface {
    public static function isValid(string $text): bool;
    public function runAction(Message $message): Message;
}