<?php

namespace App\DTO;

use App\Service\MailService;

class MailDto
{
    private string $to;

    private array $dynamicTemplateData;

    private string $templateId;

    private string $subject;

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * @param string $to
     * @return MailDto
     */
    public function setTo(string $to): self
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @return array
     */
    public function getDynamicTemplateData(): array
    {
        return $this->dynamicTemplateData;
    }

    /**
     * @param array $dynamicTemplateData
     * @return MailDto
     */
    public function setDynamicTemplateData(array $dynamicTemplateData): self
    {
        $this->dynamicTemplateData = $dynamicTemplateData;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateId(): string
    {
        return $this->templateId;
    }

    /**
     * @param string $templateId
     * @return MailDto
     */
    public function setTemplateId(string $templateId): self
    {
        $this->templateId = $templateId;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return MailDto
     */
    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @throws \JsonException
     */
    public function prepareSelf(): array
    {
        return [
            'from' => ['email' => MailService::FROM],
            'personalizations' => [[
                'to' => [['email' => $this->to]],
                'subject' => $this->subject,
                'dynamic_template_data' => $this->dynamicTemplateData
            ]],
            'template_id' => $this->templateId
        ];
    }
}