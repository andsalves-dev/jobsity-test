<?php

namespace App\Validator\Constraints;

use App\Repository\UserRepository;
use App\Traits\EntityManagerAwareTrait;
use Symfony\Component\Validator\Constraints\{Callback,
    Collection,
    CollectionValidator,
    Email,
    GreaterThanOrEqual,
    Length,
    NotBlank,
    Regex};
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UserConstraints extends Collection {
    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;

        parent::__construct([
            'username' => [
                new NotBlank(),
                $this->createUniqueConstraint('username'),
                new Regex(['pattern' => '/^[a-zA-Z0-9_-]+$/'])
            ],
            'email' => [new Email(), new NotBlank(), $this->createUniqueConstraint('email')],
            'name' => [new NotBlank()],
            'password' => [new NotBlank(), new Length(['min' => 4, 'max' => 64])],
            'default_currency' => [new NotBlank(), new Length(3)],
        ]);
    }

    public function validatedBy() {
        return CollectionValidator::class;
    }

    private function createUniqueConstraint(string $field) {
        return new Callback(function ($value, ExecutionContextInterface $context) use ($field) {
            $found = $this->userRepository->findOneBy([$field => $value]);

            if ($found) {
                $context->buildViolation("The $field '$value' is already registered.")->addViolation();
            }
        });
    }
}