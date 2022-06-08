<?php

namespace App\Entity;

use App\Repository\DestinationCommentRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DestinationCommentRepository::class)]
class DestinationComment
{
    public const GROUP_READ = 'comment:read';
    public const GROUP_PATCH = 'comment:patch';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups([self::GROUP_READ, Destination::GROUP_READ])]
    private int $id;

    #[ORM\Column(type: 'text')]
    #[Groups([self::GROUP_READ, Destination::GROUP_READ, self::GROUP_PATCH])]
    #[Assert\NotBlank(message: 'Tekst komentara ne sme biti prazan')]
    private string $body;

    #[ORM\Column(type: 'datetime')]
    #[Groups([self::GROUP_READ, Destination::GROUP_READ])]
    #[Assert\NotBlank(message: 'Datum kreiranja komentara ne sme biti prazan')]
    private DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: Destination::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::GROUP_READ])]
    #[Assert\NotBlank(message: 'Destinacija vezana za komentara ne sme biti prazna')]
    private Destination $destination;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'destinationComments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::GROUP_READ, Destination::GROUP_READ])]
    #[Assert\NotBlank(message: 'Korisnik koji postavlja komentar ne sme biti prazan')]
    private User $user;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDestination(): ?Destination
    {
        return $this->destination;
    }

    public function setDestination(?Destination $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
