<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity("email", message: "Uneti email je već zauzet")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    public const GROUP_REGISTER = 'register';
    public const GROUP_READ = 'user:read';
    public const GROUP_PATCH = 'user:patch';

    public const DEFAULT_AVATAR = '/profiles/kimberly-farmer-lUaaKCUANVI-unsplash-629e4536a219a1.35127565.jpg';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups([self::GROUP_READ, Destination::GROUP_READ, DestinationComment::GROUP_READ, WishList::GROUP_READ])]
    private int $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\Email(message: 'Loš format emaila')]
    #[Assert\NotBlank(message: 'Polje ne sme da bude prazno')]
    #[Groups([self::GROUP_REGISTER, self::GROUP_READ])]
    private string $email = '';

    #[ORM\Column(type: 'json')]
    #[Groups([self::GROUP_READ])]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private string $password;

    #[Assert\NotBlank(message: "Polje ne sme da bude prazno")]
    #[Assert\Length(min: 8, minMessage: 'Lozinka je prekratka (min 8 karaktera)')]
    #[Groups([self::GROUP_REGISTER])]
    private string|null $plainPassword = '';

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Ime je prekratko', maxMessage: 'Ime je predugačko')]
    #[Assert\Regex(pattern: "/^[a-zA-Z\s]*$/", message: 'Samo su slova dovoljena')]
    #[Groups([self::GROUP_REGISTER, self::GROUP_READ, self::GROUP_PATCH, Destination::GROUP_READ])]
    private string $firstname = '';

    #[Assert\Length(min: 2, max: 100, minMessage: 'Prezime je prekratko', maxMessage: 'Prezime je predugačko')]
    #[Assert\Regex(pattern: '/^[a-zA-Z\s]*$/', message: 'Samo su slova dovoljena')]
    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([self::GROUP_REGISTER, self::GROUP_READ, self::GROUP_PATCH, Destination::GROUP_READ])]
    private string $lastname = '';

    #[ORM\Column(type: 'boolean')]
    private bool $verified = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string|null $verificationToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTimeInterface|null $verificationTokenExpire = null;

    #[ORM\ManyToOne(targetEntity: City::class, inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::GROUP_READ])]
    private City $city;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([self::GROUP_READ])]
    private string $avatar = self::DEFAULT_AVATAR;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: DestinationComment::class, orphanRemoval: true)]
    private ArrayCollection|PersistentCollection $destinationComments;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: WishList::class, orphanRemoval: true)]
    private ArrayCollection|PersistentCollection $wishLists;

    public function __construct()
    {
        $this->destinationComments = new ArrayCollection();
        $this->wishLists = new ArrayCollection();
    }

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

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @return Collection<int, DestinationComment>
     */
    public function getDestinationComments(): Collection
    {
        return $this->destinationComments;
    }

    public function addDestinationComment(DestinationComment $destinationComment): self
    {
        if (!$this->destinationComments->contains($destinationComment)) {
            $this->destinationComments[] = $destinationComment;
            $destinationComment->setUser($this);
        }

        return $this;
    }

    public function removeDestinationComment(DestinationComment $destinationComment): self
    {
        if ($this->destinationComments->removeElement($destinationComment)) {
            // set the owning side to null (unless already changed)
            if ($destinationComment->getUser() === $this) {
                $destinationComment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, WishList>
     */
    public function getWishLists(): Collection
    {
        return $this->wishLists;
    }

    public function addWishList(WishList $wishList): self
    {
        if (!$this->wishLists->contains($wishList)) {
            $this->wishLists[] = $wishList;
            $wishList->setUser($this);
        }

        return $this;
    }

    public function removeWishList(WishList $wishList): self
    {
        if ($this->wishLists->removeElement($wishList)) {
            // set the owning side to null (unless already changed)
            if ($wishList->getUser() === $this) {
                $wishList->setUser(null);
            }
        }

        return $this;
    }
}
