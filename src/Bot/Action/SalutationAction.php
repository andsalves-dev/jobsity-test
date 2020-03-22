<?php


namespace App\Bot\Action;


use App\Entity\Message;

class SalutationAction extends AbstractBotAction {

    private static $pattern = "/(Hey|Hello|Hola|Hey|Hi)(.*){0,5}$/i";

    /**
     * @param Message $message
     * @return Message
     * @throws \Exception
     */
    public function runAction(Message $message): Message {
        $responseMessage = $this->messageFactory->create([
            'text' => 'Hey there. How can I help you?',
            'is_bot' => true,
        ], $message->getUser());

        $this->entityManager->persist($responseMessage);

        $this->entityManager->flush();
        return $responseMessage;
    }

    public static function isValid(string $text): bool {
        return (bool)preg_match(self::$pattern, $text);
    }
}