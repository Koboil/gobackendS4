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
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Filter\CompanySearchFilter;
 use App\Repository\CompanyRepository;
use App\State\UserProcessor;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(security: 'is_granted(\'view\',object)'),
        new Put(security: 'is_granted(\'edit\',object)'),
        new Delete(security: 'is_granted(\'edit\',object)'),
        new Patch,

        new GetCollection(),
        new Post(),
    ],
    normalizationContext: ['groups' => ['company:read']],
    denormalizationContext: ['groups' => ['company:write']],
    paginationClientEnabled: true,
    paginationClientItemsPerPage: true,
    paginationEnabled: true,
    processor: UserProcessor::class
)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity("name")]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['id', 'name', 'city', 'country', 'postalCode', 'state', 'createdAt', 'updatedAt'])]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['id' => 'exact', 'name' => 'partial', 'line2' => 'partial', 'postalCode' => 'partial', 'state' => 'partial', 'city' => 'partial', 'country' => 'partial'])]
#[ApiFilter(filterClass: DateFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ORM\UniqueConstraint(fields: ['name'])]
#[ApiFilter(filterClass: CompanySearchFilter::class)]
#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    const MERCURE_TOPIC = "/api/companies";
    const READ = "company:read";
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["company:read", "company:write","service:read","company_user:read"])]
    private ?int $id = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Sequentially([
        new Assert\Length(max: 250),
    ])]
    #[Groups(["company:read", "company:write","service:read","company_user:read"])]
    /**
     * the name of the address in case the billing name is different of the user name
     */
    private ?string $name = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Sequentially([
        new Assert\Length(max: 250)
    ])]
    #[Groups(["company:read", "company:write","company_user:read"])]
    private ?string $line1 = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Sequentially([
        new Assert\Length(max: 250)
    ])]
    #[Groups(["company:read", "company:write","company_user:read"])]
    private ?string $line2 = null;

    #[ORM\Column(type: 'string', length: 100,nullable: true)]
    #[Assert\Sequentially([
        new Assert\Length(max: 250)
    ])]
    #[Groups(["company:read", "company:write","company_user:read"])]
    private string $city;

    #[ORM\Column(type: 'string', length: 10,nullable: true)]
    #[Assert\Sequentially([
        new Assert\Length(min: 1, max: 10)
    ])]
    #[Groups(["company:read", "company:write","company_user:read"])]
    private ?string $postalCode;

    #[ORM\Column(type: 'string', length: 100,nullable: true)]
    #[Assert\Sequentially([
        new Assert\Length(max: 250),
        new Assert\Country()
    ])]
    #[Groups(["company:read", "company:write","company_user:read"])]
    private string $country;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Assert\Sequentially([
        new Assert\Length(max: 250)
    ])]
    #[Groups(["company:read", "company:write","company_user:read"])]
    private ?string $state = null; // optional if needed

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: CompanyUser::class)]
    private Collection $members;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["company:read", "company:write","company_user:read"])]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["company:read", "company:write"])]
    private ?string $image = null;

    #[ORM\Column(options: ["default"=> false])]
    #[Groups(["company:read", "company:write"])]
    private bool $isActive = false;

     #[ApiProperty(iris: ['https://schema.org/image'], openapiContext: ['type' => 'string'])]
    #[ORM\ManyToOne(targetEntity: MediaObject::class)]
     #[ORM\JoinColumn(nullable: true)]
    #[Groups(["company:read", "company:write"])]

    private ?MediaObject $logo = null;

    #[ORM\Column]
    #[Groups(["company:read"])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["company:read"])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["company:read"])]
    /**
     * représente the kbis number
     */
    private ?string $registrationNumber = null;

    public function __construct()
    {
        $this->members = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLine1(): ?string
    {
        return $this->line1;
    }

    public function setLine1(?string $line1): static 
    {
        $this->line1 = $line1;
        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): static 
    {
        $this->city = $city;
        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): static 
    {
        $this->country = $country;
        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static 
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): static 
    {
        $this->state = $state;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static 
    {
        $this->name = $name;
        return $this;
    }

    public function getLine2(): ?string
    {
        return $this->line2;
    }

    public function setLine2(?string $line2): static 
    {
        $this->line2 = $line2;
        return $this;
    }

    public function addMember(CompanyUser $member): static 
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
            $member->setCompany($this);
        }

        return $this;
    }

    public function removeMember(CompanyUser $member): static 
    {
        if ($this->members->removeElement($member)) {
            // set the owning side to null (unless already changed)
            if ($member->getCompany() === $this) {
                $member->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CompanyUser>
     */

    public function getMembers(): Collection
    {
        return
            $this->members;

    }
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getLogo(): ?MediaObject
    {
        return $this->logo;
    }

    public function setLogo(?MediaObject $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(?string $registrationNumber): static
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
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
