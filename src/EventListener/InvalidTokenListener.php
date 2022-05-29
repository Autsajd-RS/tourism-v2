<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;

class InvalidTokenListener
{
    public function onTokenInvalid(JWTInvalidEvent $event): void
    {
        $response = new JWTAuthenticationFailureResponse('Invalid token', 403);
        $event->setResponse($response);
    }
}