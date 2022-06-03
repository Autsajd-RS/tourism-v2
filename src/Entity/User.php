<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\Email(message: 'Invalid email format')]
    #[Assert\NotBlank(message: 'Invalid email format')]
    #[Groups(['register'])]
    private string $email = '';

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private string $password;

    #[Assert\Regex(
        pattern: "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/",
        message: 'Invalid password'
    )]
    #[Groups(['register'])]
    private string|null $plainPassword = '';

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Name too short', maxMessage: 'Name too long')]
    #[Assert\Regex(pattern: "/^[a-zA-Z\s]*$/", message: 'Only letters allowed')]
    #[Groups(['register'])]
    private string $firstname = '';

    #[Assert\Length(min: 2, max: 100, minMessage: 'Last Name too short', maxMessage: 'Last Name too long')]
    #[Assert\Regex(pattern: '/^[a-zA-Z\s]*$/', message: 'Only letters allowed')]
    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['register'])]
    private string $lastname = '';

    #[ORM\Column(type: 'boolean')]
    private bool $verified = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string|null $verificationToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTimeInterface|null $verificationTokenExpire = null;

    #[ORM\ManyToOne(targetEntity: City::class, inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private $city;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     * @return User
     */
    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): self
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->verified;
    }

    public function setVerified(bool $emailVerified): self
    {
        $this->verified = $emailVerified;

        return $this;
    }

    public function getVerificationToken(): ?string
    {
        return $this->verificationToken;
    }

    public function setVerificationToken(?string $emailToken): self
    {
        $this->verificationToken = $emailToken;

        return $this;
    }

    public function getVerificationTokenExpire(): ?DateTimeInterface
    {
        return $this->verificationTokenExpire;
    }

    public function setVerificationTokenExpire(?DateTimeInterface $emailTokenExpire): self
    {
        $this->verificationTokenExpire = $emailTokenExpire;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }
}
