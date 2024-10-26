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
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Filter\ReservationSearchFilter;
use App\Repository\ReservationRepository;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Delete(),
        new Patch(),
        new Put()
    ],
    paginationClientEnabled: true,
    paginationClientItemsPerPage: true,
    paginationEnabled: true,
    normalizationContext: ['groups' => ['reservation:read']],
    denormalizationContext: ['groups' => ['reservation:write']],
)]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['id', 'startAt', 'endAt'])]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['id' => 'exact', 'customer' => 'exact', 'provider' => 'exact', 'reservation' => 'exact', 'reservation.name' => 'exact', 'startAt' => 'exact', 'endAt' => 'exact'])]
#[ApiFilter(filterClass: DateFilter::class, properties: ['startAt', 'endAt'])]
#[ApiFilter(filterClass: ReservationSearchFilter::class)]
class Reservation
{
    const MERCURE_TOPIC = "/api/reservations";
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["reservation:read", "reservation:write"])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(["reservation:read", "reservation:write"])]
    #[Assert\Sequentially([
        new Assert\Length(max: 500),
    ])]
    private ?string $note = null;

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[Groups(["reservation:read", "reservation:write"])]
    private Service $service;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Assert\Sequentially([
        new Assert\NotNull(),
    ])]
    #[Groups(["reservation:read", "reservation:write"])]
    /**
     * the client who book an appointment
     */
    private ?User $customer = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Assert\Sequentially([
        new Assert\NotNull(),
    ])]
    #[Groups(["reservation:read", "reservation:write"])]
    /**
     * The service provider
     */
    private ?User $provider = null;

    #[ORM\Column]
    #[Assert\Sequentially([
        new Assert\NotNull()
    ])]
    #[Groups(["reservation:read", "reservation:write"])]
    private ?\DateTimeImmutable $startAt = null;

    #[ORM\Column]
    #[Assert\Sequentially([
        new Assert\NotNull,
        new Assert\GreaterThan(propertyPath: "startAt")
    ])]
    #[Groups(["reservation:read", "reservation:write"])]
    private ?\DateTimeImmutable $endAt = null;

    #[ORM\ManyToOne()]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    #[Groups(["reservation:read", "reservation:write"])]
    private ?ReservationStatus $status = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }


    public function getUser(): ?User
    {
        return $this->customer;
    }

    public function setUser(?User $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getStartAtAgo(): string
    {
        if ($this->getStartAt() === null) return "";
        return Carbon::instance($this->getStartAt())->diffForHumans();
    }

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeImmutable $startAt): static
    {
        $this->startAt = $startAt;

        return $this;
    }

    #[Groups(["reservation:read"])]
    public function getEndAtAgo(): string
    {
        if ($this->getEndAt() === null) return "";
        return Carbon::instance($this->getEndAt())->diffForHumans();
    }

    public function getEndAt(): ?\DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeImmutable $endAt): static
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getService(): Service
    {
        return $this->service;
    }

    public function setService(Service $service): static
    {
        $this->service = $service;
        return $this;
    }

    public function getStatus(): ?ReservationStatus
    {
        return $this->status;
    }

    public function setStatus(?ReservationStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getProvider(): ?User
    {
        return $this->provider;
    }

    public function setProvider(?User $provider): Reservation
    {
        $this->provider = $provider;

        return $this;
    }

    public function getDuration(): int
    {
        if ($this->getStartAt() === null) return 0;
        if ($this->getEndAt() === null) return 0;

        $endAt = new Carbon($this->endAt);
        return $endAt->diffInMinutes($this->startAt);
    }

    public function getCustomer(): ?User
    {
        return $this->customer;
    }

    public function setCustomer(?User $customer): void
    {
        $this->customer = $customer;
    }


}
