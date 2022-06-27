<?php

namespace App\Service;

use App\DTO\MailDto;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MailService
{
    public const FROM = 'autsajdrs@gmail.com';

    public const VERIFICATION_MAIL = 'Verification email';
    public const VERIFICATION_MAIL_ID = 'd-ef39b2f70da84acb9da49bb7d933d9ba';

    public function __construct(
        private HttpClientInterface $sendgridClient,
        private LoggerInterface $logger
    )
    {
    }

    public function send(MailDto $mailDto): void
    {
        try {
            $this->sendgridClient->request(Request::METHOD_POST, '/v3/mail/send', [
                'json' => $mailDto->prepareSelf()
            ]);
        } catch (\JsonException|TransportExceptionInterface $e) {
            $this->logger->error('FAILED VERIFICATION EMAIL', ['e' => $e]);
        }
    }
}