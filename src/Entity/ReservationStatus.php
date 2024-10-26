<?php


namespace App\Entity;

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
use App\Repository\ReservationStatusRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(operations: [
    new Get(),
    new GetCollection(),
    new Post(security: "is_granted('ROLE_ADMIN')"),
    new Delete(),
    new Patch(security: "is_granted('ROLE_ADMIN')"),
    new Put(security: "is_granted('ROLE_ADMIN')")
],
    normalizationContext: ['groups' => ['reservation_status:read']], denormalizationContext: ['groups' => ['reservation_status:write']],
   )]
#[ORM\Entity(repositoryClass: ReservationStatusRepository::class)]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['id', 'status', 'color'])]
#[ApiFilter(filterClass: SearchFilter::class)]
class ReservationStatus
{ const MERCURE_TOPIC = "/api/reservation_statuses";
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(["reservation_status:read", "reservation:read"])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Sequentially([
        new Assert\NotBlank,
        new Assert\Length(max: 250)
    ])]
    #[Groups(["reservation_status:read", "reservation_status:write", "reservation:read"])]
    private ?string $name;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Sequentially([
        new Assert\Length(max: 250)
    ])]
    #[Groups(["reservation_status:read", "reservation_status:write", "reservation:read"])]
    private ?string $color;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }
}
