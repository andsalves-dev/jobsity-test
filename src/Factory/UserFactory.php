<?php

namespace App\Factory;

use App\Entity\User;
use App\Exception\CurrencyConversionException;
use App\Repository\UserRepository;
use App\Service\CurrencyExchangeService;
use App\Util\PasswordEncoder;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class UserFactory {
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
     * @return User
     * @throws CurrencyConversionException
     * @throws InvalidArgumentException
     */
    public function create(array $data): User {
        $user = new User();

        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $user->setUsername($data['username']);
        $user->setPassword(PasswordEncoder::encode($data['password']));

        if (!$this->exchangeService->validateCurrency($data['default_currency'])) {
            throw new UnprocessableEntityHttpException(sprintf(
                "'%s' is not a valid currency", $data['default_currency']
            ));
        }

        return $user->setDefaultCurrency($data['default_currency']);
    }
}