<?php

namespace App\Bot\Action;

use App\Entity\Message;
use App\Entity\User;
use App\Factory\MessageFactory;
use App\Traits\EntityManagerAwareTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

abstract class AbstractBotAction implements BotActionInterface {
    use EntityManagerAwareTrait;

    /** @var MessageFactory */
    protected $messageFactory;

    public function __construct(MessageFactory $messageFactory) {
        $this->messageFactory = $messageFactory;
    }

    protected function createResponseMessageFromException(\Exception $exception, User $user): Message {
        $responseMessage = $this->messageFactory->create([
            'text' => 'Could complete your request: ' . $exception->getMessage(),
            'is_bot' => true,
        ], $user);

        $this->entityManager->persist($responseMessage);
        $this->entityManager->flush();

        return $responseMessage;
    }
}