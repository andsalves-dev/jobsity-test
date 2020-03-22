<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraints\{Collection, Blank, CollectionValidator, Choice, Positive, Length, NotBlank};

class MessageConstraints extends Collection {

    public function __construct() {
        parent::__construct([
            'text' => [new NotBlank],
            'is_bot' => [new Choice(['choices' => [0, 1, false, true]])],
        ]);
    }

    function validatedBy() {
        return CollectionValidator::class;
    }
}