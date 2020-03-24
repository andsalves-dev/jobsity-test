<?php

namespace App\Factory;

use App\Entity\Transaction;
use App\Entity\User;
use App\Exception\CurrencyConversionException;
use App\Repository\UserRepository;
use App\Service\CurrencyExchangeService;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class TransactionFactory {
    /** @var UserRepository */
    private $userRepository;
    /** @var CurrencyExchangeService */
    private $exchangeService;

    public function __construct(UserRepository $userRepository, CurrencyExchangeService $exchangeService) {
        $this->userRepository = $userRepository;
        $this->exchangeService = $exchangeService;
    }

    /**
     * @param array $data
     * @param User $user
     * @return Transaction
     * @throws CurrencyConversionException
     * @throws InvalidArgumentException
     */
    public function create(array $data, User $user) {
        $transaction = new Transaction();

        $transaction->setUser($user);
        $transaction->setType($data['type']);
        $transaction->setAmount($data['amount']);
        $transaction->setAmountCurrency($data['amount_currency'] ?? $user->getDefaultCurrency());
        $transaction->setBalanceBefore($this->userRepository->findCurrentBalance($user));
        $this->setBalanceAfter($transaction);

        return $transaction->setDate(new \DateTime());
    }

    /**
     * @param Transaction $transaction
     * @throws CurrencyConversionException
     * @throws InvalidArgumentException
     */
    private function setBalanceAfter(Transaction $transaction) {
        $amount = $this->convertToDefaultCurrency(
            $transaction->getAmount(),
            $transaction->getAmountCurrency(),
            $transaction->getUser()->getDefaultCurrency()
        );

        if ($transaction->getType() === Transaction::DEPOSIT_TYPE) {
            $balanceAfter = $transaction->getBalanceBefore() + $amount;
        } else {
            $balanceAfter = $transaction->getBalanceBefore() - $amount;

            if ($balanceAfter < 0) throw new NotAcceptableHttpException('Not enough balance');
        }

        $transaction->setBalanceAfter(round($balanceAfter, 2));
    }

    /**
     * @param $amount
     * @param $from
     * @param $to
     * @return float
     * @throws CurrencyConversionException
     * @throws InvalidArgumentException
     */
    private function convertToDefaultCurrency($amount, $from, $to) {
        if ($from === $to) return $amount;

        return $this->exchangeService->convertToCurrency($amount, $from, $to);
    }
}