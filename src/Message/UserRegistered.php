<?php

namespace App\Message;

class UserRegistered
{
    private string $email;
    private string $verificationCode;

    public function __construct(
        string $email,
        string $verificationCode
    )
    {
        $this->email = $email;
        $this->verificationCode = $verificationCode;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getVerificationCode(): string
    {
        return $this->verificationCode;
    }
}