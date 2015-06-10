<?php

namespace GTC\Component\Security;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class PlainPasswordEncoder implements PasswordEncoderInterface
{
    public function encodePassword($raw, $salt)
    {
        return $raw;
    }
    public function isPasswordValid($encoded, $raw, $salt){
        return $encoded === $this->encodePassword($raw, $salt);
    }
}
