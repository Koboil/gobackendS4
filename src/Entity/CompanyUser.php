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
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
 use App\Repository\CompanyRepository;
use App\Repository\CompanyUserRepository;
use App\State\UserProcessor;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
 use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(security: 'is_granted(\'view\',object)'),
        new Put(security: 'is_granted(\'edit\',object)'),
        new Delete(security: 'is_granted(\'edit\',object)'),
        new Patch,
        new GetCollection(),
        new GetCollection(
            uriTemplate: '/users/{id}/company_users',
            uriVariables: [
                'id' => new Link (fromProperty: 'id', toProperty: 'user', fromClass: CompanyUser::class)
            ]
        ),
        new Post(),
    ],
    normalizationContext: ['groups' => ['company_user:read']],
    denormalizationContext: ['groups' => ['company_user:write']],
    paginationClientEnabled: true,
    paginationClientItemsPerPage: true,
    paginationEnabled: true,
)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(['company','user'])]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['id' => 'exact', 'user' => 'exact',  'company' => 'exact' ])]
#[ApiFilter(filterClass: DateFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['createdAt'])]
#[ORM\UniqueConstraint(  fields: ['company','user'])]
#[ORM\Entity(repositoryClass: CompanyUserRepository::class)]
class CompanyUser
{
    public const ROLE_ADMIN ="ROLE_ADMIN";
    public const ROLE_USER ="ROLE_USER";
    const MERCURE_TOPIC = "/api/company_users";
    const   READ = "company_user:read";
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["company_user:read", "company_user:write"])]
    private ?int $id = null;
    #[ORM\ManyToOne(targetEntity: User::class,inversedBy: "companies")]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["company_user:read", "company_user:write"])]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Company::class,inversedBy: "members")]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["company_user:read", "company_user:write"])]
    private Company $company;

    #[ORM\Column]
    #[Groups(["company_user:read", "company_user:write"])]
    private string $role = self::ROLE_USER;
    #[ORM\Column]
    #[Groups(["company_user:read"])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["company_user:read"])]
    private ?\DateTimeImmutable $updatedAt = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }



    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }


    #[Groups(["company:read"])]
    public function getCreatedAtAgo(): string
    {
        if ($this->createdAt === null) {
            return "";
        }
        return Carbon::instance($this->createdAt)->diffForHumans();
    }

    #[Groups(["company:read"])]
    public function getUpdatedAtAgo(): string
    {
        if ($this->updatedAt === null) {
            return "";
        }
        return Carbon::instance($this->updatedAt)->diffForHumans();
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): static
    {
        $this->company = $company;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;    return $this;
    }
}
