<?php


namespace App\Bot\Action;


use App\Entity\Message;
use App\Entity\User;
use App\Repository\UserRepository;

class CheckBalanceAction extends AbstractBotAction {

    private static $pattern = "/(check|show|display|)(\s|)(current\s|)(balance)$/i";

    /**
     * @param Message $message
     * @return Message
     * @throws \Exception
     */
    public function runAction(Message $message): Message {
        /** @var UserRepository $userRepo */
        $userRepo = $this->entityManager->getRepository(User::class);
        $responseMessage = $this->messageFactory->create([
            'text' => sprintf(
                'Your current balance is %s %s',
                number_format($userRepo->findCurrentBalance($message->getUser()), 2),
                $message->getUser()->getDefaultCurrency()
            ),
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