<?php

namespace App\Factory;

use App\Entity\Message;
use App\Entity\User;

class MessageFactory implements EntityFactoryInterface {

    public function create(array $data, User $user) {
        $message = new Message();

        $message->setUser($user);
        $message->setIsBot((bool)$data['is_bot']);
        $message->setText($data['text']);

        return $message->setDate(new \DateTime());
    }
}