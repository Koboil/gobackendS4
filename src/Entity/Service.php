<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Filter\ServiceSearchFilter;
use App\Repository\ServiceRepository;
use Carbon\Carbon;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(operations: [
    new Get(), new Put(),
    new Delete(), new GetCollection(),
    new Post()],
    normalizationContext: ['groups' => ['service:read']], denormalizationContext: ['groups' => ['service:write']], paginationClientEnabled: true,
    paginationClientItemsPerPage: true,
    paginationEnabled: true,
    )]
#[ApiFilter(filterClass: OrderFilter::class)]
#[ApiFilter(filterClass: SearchFilter::class, properties: [
    'id' => 'exact',
    'name' => 'partial',
    'description' => 'partial',
    'company' => 'exact',
])]
#[ApiFilter(DateFilter::class, properties: ['updatedAt'])]
#[ApiFilter(filterClass: ServiceSearchFilter::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    const MERCURE_TOPIC = "/api/services";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["service:read", "service:write", "review:read", "reservation:read"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Sequentially([
        new Assert\NotBlank,
        new Assert\Length(max: 200)
    ])]
    #[Groups(["service:read", "service:write", "review:read", "reservation:read"])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Sequentially([
        new Assert\Length(max: 5000)
    ])]
    #[Groups(["service:read", "service:write", "review:read", "reservation:read"])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["service:read", "service:write"])]
    private ?string $price = null;

    #[ApiProperty(iris: ['https://schema.org/image'], openapiContext: ['type' => 'string'])]
    #[ORM\ManyToOne(targetEntity: MediaObject::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(["service:read", "service:write", "review:read", "reservation:read"])]
    private ?MediaObject $image = null;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(["service:read", "service:write"])]
    private ?Company $company = null;

    #[ORM\Column]
    #[Groups(["service:read"])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["service:read"])]
    private ?\DateTimeImmutable $updatedAt = null;
    #[ORM\Column()]
    #[Groups(["service:read", "service:write"])]
    private bool $isActive = false;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getImage(): ?MediaObject
    {
        return $this->image;
    }

    public function setImage(?MediaObject $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;
        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updatedTimestamps(): void
    {
        $this->updatedAt = new \DateTimeImmutable('now');
        if ($this->getCreatedAt() === null) {
            $this->createdAt = new \DateTimeImmutable('now');
        }
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[Groups(["service:read"])]
    public function getCreatedAtAgo(): string
    {
        if ($this->createdAt === null) {
            return "";
        }
        return Carbon::instance($this->createdAt)->diffForHumans();
    }

    #[Groups(["service:read"])]
    public function getUpdatedAtAgo(): string
    {
        if ($this->updatedAt === null) {
            return "";
        }
        return Carbon::instance($this->updatedAt)->diffForHumans();
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }
}
