<?php

namespace App\DTO;

class ErrorResponse
{
    private string $message;

    private array $errors;

    public function __construct(
        string $message,
        array $errors = []
    )
    {
        $this->message = $message;
        $this->errors = $errors;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return ErrorResponse
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     * @return ErrorResponse
     */
    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }
}