<?php

namespace App\DTO;
use Symfony\Component\Validator\Constraints as Assert;

class ForgotPasswordCredentials
{
    #[Assert\NotBlank(message: "Polje ne sme da bude prazno")]
    #[Assert\Length(min: 8, minMessage: 'Lozinka je prekratka (min 8 karaktera)')]
    private string $newPassword;

    #[Assert\NotBlank(message: "Polje ne sme da bude prazno")]
    #[Assert\Expression(
        expression: 'this.getRepeatedPassword() === this.getNewPassword()'
    )]
    private string $repeatedPassword;

    /**
     * @return string
     */
    public function getNewPassword(): string
    {
        return $this->newPassword;
    }

    /**
     * @param string $newPassword
     * @return ForgotPasswordCredentials
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
     * @return ForgotPasswordCredentials
     */
    public function setRepeatedPassword(string $repeatedPassword): self
    {
        $this->repeatedPassword = $repeatedPassword;
        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->newPassword = '';
        $this->repeatedPassword = '';
    }
}