<?php

namespace App\MessageHandler;

use App\DTO\MailDto;
use App\Message\UserRegistered;
use App\Service\MailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UserRegisteredHandler
{
    public function __construct(
        private MailService $mailService,
        private string $baseUrl
    )
    {
    }

    public function __invoke(UserRegistered $message)
    {
        $email = (new MailDto())
            ->setTo($message->getEmail())
            ->setSubject(MailService::VERIFICATION_MAIL)
            ->setTemplateId(MailService::VERIFICATION_MAIL_ID)
            ->setDynamicTemplateData([
                'verificationUrl' => $this->baseUrl . '/api/verify/' . $message->getVerificationCode()
            ]);

        $this->mailService->send($email);
    }
}