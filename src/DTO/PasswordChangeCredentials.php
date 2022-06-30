<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class PasswordChangeCredentials
{
    #[Assert\NotBlank(message: 'Polje ne sme da bude prazno')]
    private string $currentPassword;

    #[Assert\NotBlank(message: "Polje ne sme da bude prazno")]
    #[Assert\Length(min: 8, minMessage: 'Lozinka je prekratka (min 8 karaktera)')]
    private string $newPassword;

    private string $repeatedPassword;

    /**
     * @return string
     */
    public function getCurrentPassword(): string
    {
        return $this->currentPassword;
    }

    /**
     * @param string $currentPassword
     * @return PasswordChangeCredentials
     */
    public function setCurrentPassword(string $currentPassword): self
    {
        $this->currentPassword = $currentPassword;
        return $this;
    }

    /**
     * @return string
     */
    public function getNewPassword(): string
    {
        return $this->newPassword;
    }

    /**
     * @param string $newPassword
     * @return PasswordChangeCredentials
     */
    public function setNewPassword(string $newPassword): self
    {
        $this->newPassword = $newPassword;
        return $this;
    }

    /**
     * @return string
     */
    public function getRepeatedPassword(): string
    {
        return $this->repeatedPassword;
    }

    /**
     * @param string $repeatedPassword
     * @return PasswordChangeCredentials
     */
    public function setRepeatedPassword(string $repeatedPassword): self
    {
        $this->repeatedPassword = $repeatedPassword;
        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->currentPassword = '';
        $this->newPassword = '';
        $this->repeatedPassword = '';
    }
}