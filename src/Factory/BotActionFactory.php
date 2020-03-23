<?php

namespace App\Factory;

use App\Bot\Action\BotActionInterface;
use Psr\Container\ContainerInterface;

class BotActionFactory {
    /** @var ContainerInterface */
    protected $serviceLocator;

    public function __construct(ContainerInterface $container) {
        $this->serviceLocator = $container;
    }

    public function create(string $botActionClass): BotActionInterface {
        return $this->serviceLocator->get($botActionClass);
    }
}