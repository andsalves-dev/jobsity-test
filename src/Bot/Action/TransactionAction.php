<?php

namespace App\Bot\Action;

use App\Entity\Message;
use App\Entity\Transaction;
use App\Entity\User;
use App\Factory\MessageFactory;
use App\Factory\TransactionFactory;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class TransactionAction extends AbstractBotAction {

    private static $pattern = "/(.*)(?<action>(deposit|withdrawal|withdraw))(.*)"
    . "\s(?<amount>(\d+(\.\d{1,2}|)))(\s|)(?<currency>(([a-z]{3})|))(\,|)(\s*|\s.+)(\?|\.)*$/i";

    /** @var TransactionFactory */
    protected $transactionFactory;

    /**
     * @param Message $message
     * @return Message
     * @throws InvalidArgumentException
     */
    public function runAction(Message $message): Message {
        try {
            $params = $this->extractParams($message->getText(), $message->getUser());
            $transaction = $this->transactionFactory->create($params, $message->getUser());

            $this->entityManager->persist($transaction);
            $this->entityManager->flush();

            $responseMessage = $this->messageFactory->create([
                'text' => sprintf(
                    '%s completed successfully. Your new balance is: %s %s',
                    $params['type'] === Transaction::DEPOSIT_TYPE ? 'Deposit' : 'Withdrawal',
                    $transaction->getBalanceAfter(),
                    $message->getUser()->getDefaultCurrency()
                ),
                'is_bot' => true,
            ], $message->getUser());

            $this->entityManager->persist($responseMessage);
            $this->entityManager->flush();

            return $responseMessage;
        } catch (NotAcceptableHttpException $exception) {
            return $this->createResponseMessageFromException($exception, $message->getUser());
        } catch (\Throwable $exception) {
            throw new BadRequestHttpException('Could not process the request: ' . $exception->getMessage(), $exception);
        }
    }

    public static function isValid(string $text): bool {
        return (bool)preg_match(self::$pattern, $text);
    }

    private function extractParams(string $text, User $user) {
        preg_match(self::$pattern, $text, $matches);
        return [
            'type' => strtolower($matches['action']) === 'deposit'
                ? Transaction::DEPOSIT_TYPE
                : Transaction::WITHDRAWAL_TYPE,
            'amount' => floatval($matches['amount']),
            'amount_currency' => $matches['currency'] !== ''
                ? strtoupper($matches['currency'])
                : $user->getDefaultCurrency()
        ];
    }

    public function __construct(MessageFactory $messageFactory, TransactionFactory $transactionFactory) {
        parent::__construct($messageFactory);

        $this->transactionFactory = $transactionFactory;
    }
}