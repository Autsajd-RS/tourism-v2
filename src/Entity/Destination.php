<?php

namespace App\Entity;

use App\Repository\DestinationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DestinationRepository::class)]
class Destination
{
    public const GROUP_READ = 'destination:read';
    public const GROUP_PATCH = 'destination:patch';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups([self::GROUP_READ, DestinationComment::GROUP_READ, WishList::GROUP_READ])]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([self::GROUP_READ, self::GROUP_PATCH])]
    #[Assert\NotBlank(message: 'Naziv destinacije ne sme biti prazan')]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([self::GROUP_READ, self::GROUP_PATCH])]
    #[Assert\NotBlank(message: 'Adresa destinacije ne sme biti prazna')]
    private string $address;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([self::GROUP_READ, self::GROUP_PATCH])]
    #[Assert\NotBlank(message: 'Longituda destinacije ne sme biti prazna')]
    private string $longitude;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([self::GROUP_READ, self::GROUP_PATCH])]
    #[Assert\NotBlank(message: 'Latituda destinacije ne sme biti prazna')]
    private string $latitude;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups([self::GROUP_READ, self::GROUP_PATCH])]
    private string|null $description;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups([self::GROUP_READ, self::GROUP_PATCH])]
    private int $popularity = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups([self::GROUP_READ, self::GROUP_PATCH])]
    private int $attendance = 0;

    #[ORM\ManyToOne(targetEntity: City::class, inversedBy: 'destinations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::GROUP_READ])]
    #[Assert\NotBlank(message: 'Grad destinacije mora biti definisan')]
    private City $city;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'destinations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::GROUP_READ])]
    #[Assert\NotBlank(message: 'Kategorija destinacije mora biti definisana')]
    private Category $category;

    #[ORM\OneToMany(mappedBy: 'destination', targetEntity: DestinationImage::class, orphanRemoval: true)]
    #[Groups([self::GROUP_READ])]
    private ArrayCollection|PersistentCollection $additionalImages;

    #[ORM\OneToMany(mappedBy: 'destination', targetEntity: DestinationComment::class, orphanRemoval: true)]
    #[Groups([self::GROUP_READ])]
    private ArrayCollection|PersistentCollection $comments;

    #[ORM\ManyToMany(targetEntity: WishList::class, mappedBy: 'destinations')]
    private ArrayCollection|PersistentCollection $wishLists;

    //virtual property
    #[Groups([self::GROUP_READ])]
    private bool $likedByMe = false;

    //virtual property
    #[Groups([self::GROUP_READ])]
    private bool $nearMe = false;

    public function __construct()
    {
        $this->additionalImages = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->wishLists = new ArrayCollection();
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPopularity(): ?int
    {
        return $this->popularity;
    }

    public function setPopularity(?int $popularity): self
    {
        $this->popularity = $popularity;

        return $this;
    }

    public function getAttendance(): ?int
    {
        return $this->attendance;
    }

    public function setAttendance(?int $attendance): self
    {
        $this->attendance = $attendance;

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, DestinationImage>
     */
    public function getAdditionalImages(): Collection
    {
        return $this->additionalImages;
    }

    public function addAdditionalImage(DestinationImage $additionalImage): self
    {
        if (!$this->additionalImages->contains($additionalImage)) {
            $this->additionalImages[] = $additionalImage;
            $additionalImage->setDestination($this);
        }

        return $this;
    }

    public function removeAdditionalImage(DestinationImage $additionalImage): self
    {
        if ($this->additionalImages->removeElement($additionalImage)) {
            // set the owning side to null (unless already changed)
            if ($additionalImage->getDestination() === $this) {
                $additionalImage->setDestination(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DestinationComment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(DestinationComment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setDestination($this);
        }

        return $this;
    }

    public function removeComment(DestinationComment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getDestination() === $this) {
                $comment->setDestination(null);
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
            $wishList->addDestination($this);
        }

        return $this;
    }

    public function removeWishList(WishList $wishList): self
    {
        if ($this->wishLists->removeElement($wishList)) {
            $wishList->removeDestination($this);
        }

        return $this;
    }

    public function isLikedByMe(): bool
    {
        return $this->likedByMe;
    }

    public function setLikedByMe(bool $likedByMe): self
    {
        $this->likedByMe = $likedByMe;

        return $this;
    }

    public function isNearMe(): bool
    {
        return $this->nearMe;
    }

    public function setNearMe(bool $nearMe): self
    {
        $this->nearMe = $nearMe;

        return $this;
    }
}
