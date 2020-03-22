<?php

namespace App\Factory;

use App\Entity\User;

interface EntityFactoryInterface {
    public function create(array $data, User $user);
}