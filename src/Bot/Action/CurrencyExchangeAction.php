<?php

namespace App\Bot\Action;

use App\Entity\Message;
use App\Entity\Transaction;
use App\Exception\CurrencyConversionException;
use App\Factory\MessageFactory;
use App\Factory\TransactionFactory;
use App\Service\CurrencyExchangeService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class CurrencyExchangeAction extends AbstractBotAction {

    private static $pattern = "/(convert|exchange|what\'s|what\sis|)(\s|)"
    . "(?<amount>((\d+(\.\d{1,2}|))|))((\s|)(?<from_currency>[a-z]{3}))"
    . "\s(in|to)\s(?<to_currency>[a-z]{3})(\s|)((\,(\s|)please)|)(\?+|\.|)$/i";

    /** @var CurrencyExchangeService */
    private $currencyExchangeService;

    /**
     * @param Message $message
     * @return Message
     * @throws InvalidArgumentException
     */
    public function runAction(Message $message): Message {
        try {
            list($amount, $from, $to) = $this->extractParams($message->getText());

            $convertedAmount = $this->currencyExchangeService->convertToCurrency($amount, $from, $to);

            $responseMessage = $this->messageFactory->create([
                'text' => sprintf(
                    "%s {$from} equals %s {$to}",
                    number_format($amount, 2),
                    number_format($convertedAmount, 2)
                ),
                'is_bot' => true,
            ], $message->getUser());

            $this->entityManager->persist($responseMessage);
            $this->entityManager->flush();

            return $responseMessage;
        } catch (CurrencyConversionException $exception) {
            return $this->createResponseMessageFromException($exception, $message->getUser());
        } catch (\Throwable $exception) {
            throw new BadRequestHttpException('Could not process the request: ' . $exception->getMessage(), $exception);
        }
    }

    public static function isValid(string $text): bool {
        return (bool)preg_match(self::$pattern, $text);
    }


    private function extractParams(string $text) {
        preg_match(self::$pattern, $text, $matches);
        return [
            floatval($matches['amount'] === '' ? 1 : $matches['amount']),
            strtoupper($matches['from_currency']),
            strtoupper($matches['to_currency']),
        ];
    }

    public function __construct(MessageFactory $messageFactory, CurrencyExchangeService $currencyExchangeService) {
        parent::__construct($messageFactory);

        $this->currencyExchangeService = $currencyExchangeService;
    }
}