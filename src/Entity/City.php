<?php

namespace App\Entity;

use App\Repository\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: CityRepository::class)]
class City
{
    public const SERIALIZER_GROUP_CITY_LIST = "city:list";
    public const SUBOTICA_ID = 6;


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups([self::SERIALIZER_GROUP_CITY_LIST, User::GROUP_READ, Destination::GROUP_READ])]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([self::SERIALIZER_GROUP_CITY_LIST, User::GROUP_READ, Destination::GROUP_READ])]
    private string $name;

    #[ORM\Column(type: 'float')]
    #[Groups([self::SERIALIZER_GROUP_CITY_LIST, User::GROUP_READ, Destination::GROUP_READ])]
    private float $lat;

    #[ORM\Column(type: 'float')]
    #[Groups([self::SERIALIZER_GROUP_CITY_LIST, User::GROUP_READ, Destination::GROUP_READ])]
    private float $lng;

    #[ORM\OneToMany(mappedBy: 'city', targetEntity: User::class)]
    private ArrayCollection|PersistentCollection $users;

    #[ORM\OneToMany(mappedBy: 'city', targetEntity: Destination::class, orphanRemoval: true)]
    private ArrayCollection|PersistentCollection $destinations;

    public function __construct()
    {
        $this->users = new ArrayCollection();
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

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLng(): ?float
    {
        return $this->lng;
    }

    public function setLng(float $lng): self
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setCity($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCity() === $this) {
                $user->setCity(null);
            }
        }

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
            $destination->setCity($this);
        }

        return $this;
    }

    public function removeDestination(Destination $destination): self
    {
        if ($this->destinations->removeElement($destination)) {
            // set the owning side to null (unless already changed)
            if ($destination->getCity() === $this) {
                $destination->setCity(null);
            }
        }

        return $this;
    }
}
