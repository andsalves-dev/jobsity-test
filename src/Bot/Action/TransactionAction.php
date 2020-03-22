<?php

namespace App\Bot\Action;

use App\Entity\Message;
use App\Entity\Transaction;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class TransactionAction extends AbstractBotAction {

    private static $pattern = "/(.*)(?<action>(deposit|withdrawal|withdraw))(.*)\s(?<amount>(\d+(\.\d{1,2}|)))(\s|)(?<currency>([a-z]{3}))(\,|)(\s*|\s.+)(\?|\.)*$/i";

    /**
     * @param Message $message
     * @return Message
     * @throws \Exception
     */
    public function runAction(Message $message): Message {
        try {
            $params = $this->extractParams($message->getText());
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
            $responseMessage = $this->messageFactory->create([
                'text' => 'Could complete your request: ' . $exception->getMessage(),
                'is_bot' => true,
            ], $message->getUser());

            $this->entityManager->persist($responseMessage);
            $this->entityManager->flush();

            return $responseMessage;
        } catch (\Throwable $exception) {
            throw new BadRequestHttpException('Could not process your request: ' . $exception->getMessage(), $exception);
        }
    }

    public static function isValid(string $text): bool {
        return (bool)preg_match(self::$pattern, $text);
    }

    private function extractParams(string $text) {
        preg_match(self::$pattern, $text, $matches);
        return [
            'type' => strtolower($matches['action']) === 'deposit'
                ? Transaction::DEPOSIT_TYPE
                : Transaction::WITHDRAWAL_TYPE,
            'amount' => floatval($matches['amount']),
            'amount_currency' => strtoupper($matches['currency'])
        ];
    }
}