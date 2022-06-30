<?php

namespace App\MessageHandler;

use App\DTO\MailDto;
use App\Message\ForgotPasswordRequested;
use App\Service\MailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ForgotPasswordRequestedHandler
{
    public function __construct(
        private MailService $mailService,
        private string $frontendUrl
    )
    {
    }

    public function __invoke(ForgotPasswordRequested $message)
    {
        $email = (new MailDto())
            ->setTo($message->getEmail())
            ->setSubject(MailService::FORGOT_PASSWORD_MAIL)
            ->setTemplateId(MailService::FORGOT_PASSWORD_MAIL_ID)
            ->setDynamicTemplateData([
                'forgotPasswordUrl' => $this->frontendUrl . '/api/profiles/password/new/' . $message->getVerificationCode()
            ]);

        $this->mailService->send($email);
    }
}