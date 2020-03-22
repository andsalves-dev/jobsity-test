<?php

namespace App\Util;

use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class PasswordEncoder {
    private static $encoder;
    public static $defaultSalt = 'some_string';

    public static function encode($password) {
        if (!self::$encoder) {
            self::$encoder = new MessageDigestPasswordEncoder('sha512', true, 500);
        }

        return self::$encoder->encodePassword($password, self::$defaultSalt);
    }
}