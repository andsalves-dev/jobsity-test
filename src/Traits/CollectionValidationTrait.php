<?php

namespace App\Traits;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait CollectionValidationTrait {
    protected function validateRequest(array $data, ValidatorInterface $validator, Collection $constraints) {
        $violations = $validator->validate($data, $constraints);

        if ($violations->count()) {
            $message = $violations->get(0)->getPropertyPath() . ': ' . $violations->get(0)->getMessage();
            throw new UnprocessableEntityHttpException($message);
        }
    }
}