<?php

namespace App\Bot\Action;

use App\Entity\Message;

interface BotActionInterface {
    public static function isValid(string $text): bool;
    public function runAction(Message $message): Message;
}