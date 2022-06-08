<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[UniqueEntity("name",message: "Kategorija već postoji", )]
class Category
{
    public const GROUP_READ = 'category:read';
    public const GROUP_PATCH = 'category:patch';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups([self::GROUP_READ, Destination::GROUP_READ])]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([self::GROUP_READ, Destination::GROUP_READ, self::GROUP_PATCH])]
    #[Assert\NotBlank(message: 'Naziv kategorije ne sme biti prazan')]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([self::GROUP_READ, Destination::GROUP_READ, self::GROUP_PATCH])]
    #[Assert\NotBlank(message: 'Ikonica kategorije ne sme biti prazna')]
    private string $icon;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Destination::class)]
    private ArrayCollection|PersistentCollection $destinations;

    public function __construct()
    {
        $this->destinations = new ArrayCollection();
    }

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

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return Collection<int, Destination>
     */
    public function getDestinations(): Collection
    {
        return $this->destinations;
    }

    public function addDestination(Destination $destination): self
    {
        if (!$this->destinations->contains($destination)) {
            $this->destinations[] = $destination;
            $destination->setCategory($this);
        }

        return $this;
    }

    public function removeDestination(Destination $destination): self
    {
        if ($this->destinations->removeElement($destination)) {
            // set the owning side to null (unless already changed)
            if ($destination->getCategory() === $this) {
                $destination->setCategory(null);
            }
        }

        return $this;
    }
}
