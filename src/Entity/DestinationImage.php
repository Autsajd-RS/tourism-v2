<?php

namespace App\Entity;

use App\Repository\DestinationImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DestinationImageRepository::class)]
class DestinationImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups([Destination::GROUP_READ])]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([Destination::GROUP_READ])]
    private string $name;

    #[ORM\ManyToOne(targetEntity: Destination::class, inversedBy: 'additionalImages')]
    #[ORM\JoinColumn(nullable: false)]
    private Destination $destination;

    #[ORM\Column(type: 'boolean')]
    #[Groups([Destination::GROUP_READ])]
    private bool $main = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function isMain(): ?bool
    {
        return $this->main;
    }

    public function setMain(bool $main): self
    {
        $this->main = $main;

        return $this;
    }
}
