<?php

namespace App\Validator\Constraints;

use App\Entity\Transaction;
use Symfony\Component\Validator\Constraints\{Collection, Blank, CollectionValidator, Choice, Positive, Length, NotBlank};

class TransactionConstraints extends Collection {

    public function __construct() {
        parent::__construct([
            'amount' => [new Positive(), new NotBlank],
            'amount_currency' => [new Length(3)],
            'type' => [new Choice(['choices' => Transaction::TYPES]), new NotBlank],
        ]);
    }

    function validatedBy() {
        return CollectionValidator::class;
    }
}