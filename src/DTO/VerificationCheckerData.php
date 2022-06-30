<?php

namespace App\DTO;

use App\Service\AuthenticationService;
use DateTimeInterface;

class VerificationCheckerData
{
    private string $code;
    private DateTimeInterface $expireAt;

    public function __construct()
    {
        $this->code = AuthenticationService::verificationCode();
        $this->expireAt = (new \DateTime())->modify('+10 minutes');
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return DateTimeInterface
     */
    public function getExpireAt(): DateTimeInterface
    {
        return $this->expireAt;
    }


}