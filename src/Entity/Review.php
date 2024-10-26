<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Validator\HasMadeReservation;
use Carbon\Carbon;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use App\Entity\Service;
 use App\Filter\UuidFilter;
use App\Repository\ReviewRepository;
 use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(operations: [new Get(), new Put(), new Delete(), new GetCollection(), new Post()], normalizationContext: ['groups' => ['review:read']], denormalizationContext: ['groups' => ['review:write']], paginationClientEnabled: true)]
#[UniqueEntity(fields: [ 'user', 'type'])]
#[ORM\Entity(repositoryClass:ReviewRepository::class)]
#[ApiFilter(filterClass: OrderFilter::class)]
#[ApiFilter(filterClass: SearchFilter::class, properties: [
    'id' => 'exact',
    'user' => 'exact',
    'stars' => 'exact',
    'type' => 'partial',
    'text' => 'partial',
    'product' => 'exact',
    'product.service' => 'exact',
   ])]
#[ApiFilter(DateFilter::class, properties: ['updatedAt'])]
//#[ApiFilter(filterClass: UuidFilter::class, properties: ['id', 'user', 'product', 'product.productContents.user'])]
#[HasMadeReservation(groups: ["user"])]
#[ORM\HasLifecycleCallbacks]
class Review
{ const MERCURE_TOPIC = "/api/reviews";
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['review:read', 'review:write'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(name: "id_product", onDelete: "cascade")]
    #[Groups(['review:read', 'review:write'])]
    private ?Service $service;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Assert\Sequentially([
        new Assert\NotNull(),
     ])]
    #[Groups(['review:read', 'review:write'])]
    private ?User $user;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Sequentially([
        new Assert\NotNull,
        new Assert\Range(
            min: 1,
            max: 5,
        )
    ])]
    #[Groups(['review:read', 'review:write'])]
    private ?int $stars;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Sequentially([
        new Assert\Length(max: 5000)
    ])]
    #[Groups(['review:read', 'review:write'])]
    private ?string $text = null;

    #[ORM\Column(nullable: true )]
    #[Groups(["review:read"])]
    private ?\DateTimeImmutable $createdAt=null;
    #[ORM\Column( nullable: true)]
    #[Groups(["review:read"])]
    private  ?\DateTimeImmutable $updatedAt=null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): static 
    {
        $this->service = $service;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }


    public function setUser(?User $user): static 
    {
        $this->user = $user;

        return $this;
    }

    public function getStars(): ?int
    {
        return $this->stars;
    }


    public function setStars(?int $stars): static 
    {
        $this->stars = $stars;

        return $this;
    }
 

    public function getText(): ?string
    {
        return $this->text;
    }


    public function setText(?string $text): static 
    {
        $this->text = $text;

        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updatedTimestamps(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
        if ($this->getCreatedAt() === null) {
            $this->createdAt = new \DateTimeImmutable('now');
        }
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }



    #[Groups(["review:read"])]
    public function getCreatedAtAgo(): string
    {
        if ($this->getCreatedAt() === null)
            return "";
        return Carbon::instance($this->getCreatedAt())->diffForHumans();
    }

    public function getUpdatedAtAgo(): string
    {
        if ($this->getUpdatedAt() === null)
            return "";
        return Carbon::instance($this->getUpdatedAt())->diffForHumans();
    }
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }


}
