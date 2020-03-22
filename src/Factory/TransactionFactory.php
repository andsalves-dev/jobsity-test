<?php

namespace App\Factory;

use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class TransactionFactory implements EntityFactoryInterface {
    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

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

            if ($balanceAfter < 0) {
                throw new NotAcceptableHttpException('Not enough balance');
            }
        }

        $transaction->setBalanceAfter(round($balanceAfter, 2));
    }

    private function convertToDefaultCurrency($amount, $from, $to) {
        if ($from === $to) {
            return $amount;
        }

        // TODO: conversion
        throw new NotAcceptableHttpException('Other currencies other than USD are not supported yet.');
    }
}