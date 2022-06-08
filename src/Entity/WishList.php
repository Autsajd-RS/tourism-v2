<?php

namespace App\Entity;

use App\Repository\WishListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WishListRepository::class)]
class WishList
{
    public const FAVORITES = 'favorites';
    public const TO_VISIT = 'to_visit';

    public const GROUP_READ = 'list:read';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups([self::GROUP_READ])]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([self::GROUP_READ])]
    #[Assert\NotBlank(message: 'Tip liste mora biti definisan')]
    private string $type;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'wishLists')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::GROUP_READ])]
    private User $user;

    #[ORM\ManyToMany(targetEntity: Destination::class, inversedBy: 'wishLists')]
    #[Groups([self::GROUP_READ])]
    private ArrayCollection|PersistentCollection $destinations;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([self::GROUP_READ])]
    #[Assert\NotBlank(message: 'Naziv liste mora biti definisan')]
    private string $name;

    public function __construct()
    {
        $this->destinations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

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
        }

        return $this;
    }

    public function removeDestination(Destination $destination): self
    {
        $this->destinations->removeElement($destination);

        return $this;
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
}
