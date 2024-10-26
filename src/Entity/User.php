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
use App\Controller\ChangePasswordController;
use App\Repository\UserRepository;
use App\State\UserProcessor;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Faker\Factory;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
 use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(security: 'is_granted(\'view\',object)'),
        new Put(security: 'is_granted(\'edit\',object)'),
        new Delete(security: 'is_granted(\'edit\',object)'),
        new Patch(security: 'is_granted(\'edit\',object)'),
        new GetCollection(),
        new Post(security: 'is_granted("PUBLIC_ACCESS")',
            validationContext: ['groups' => ['user:write', 'postValidation']]),
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
    paginationClientEnabled: true,
    paginationClientItemsPerPage: true,
    paginationEnabled: true,
    processor: UserProcessor::class,

)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity("email")]
#[UniqueEntity("username")]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['id', 'email', 'pec', 'firstName', 'lastName', 'roles', 'createdAt', 'updatedAt'])]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['id' => 'exact', 'firstName' => 'partial', 'lastName' => 'partial', 'email' => 'partial', 'pec' => 'partial', 'roles' => 'partial'])]
#[ApiFilter(filterClass: DateFilter::class, properties: ['createdAt', 'updatedAt'])]
#[Table(name: '`user`')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface, JWTUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    //#[ORM\Column(type: UuidType::NAME, unique: true)]
    //#[ORM\GeneratedValue(strategy: 'CUSTOM')]
    //#[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(["user:read", "service:read","review:read","reservation:read","company_user:read"])]
    //private ?Uuid $id = null;
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\Sequentially([
        new Assert\NotBlank,
        new Assert\Email,
        new Assert\Length(max: 170)
    ])]
    #[Groups(["user:read", "user:write", "service:read","review:read","reservation:read"])]
    private ?string $email = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Sequentially([
        new Assert\Length(max: 170)
    ])]
    #[Groups(["user:read", "user:write"])]
    private ?string $username = null;


    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(["user:read", "user:write", "service:read","review:read","reservation:read","company_user:read"])]
    #[Assert\Sequentially([
        new Assert\NotBlank,
        new Assert\Length(max: 250)

    ])]
    private ?string $firstName = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(["user:read", "user:write", "service:read","review:read","reservation:read","company_user:read"])]
    #[Assert\Sequentially([
        new Assert\NotBlank,
        new Assert\Length(max: 250)
    ])]
    private ?string $lastName = null;

    #[ApiProperty(iris: ['https://schema.org/image'], openapiContext: ['type' => 'string'])]
    #[ORM\ManyToOne(targetEntity: MediaObject::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(["user:read", "user:write", "user:avatar"])]
    private ?MediaObject $avatar = null;

    #[Groups(["user:read"])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isVerified = false;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private ?string $password = null;

    #[Groups(['user:write'])]
    #[SerializedName('password')]
    //#[Assert\NotBlank(groups: ['postValidation'])]
    private ?string $plainPassword = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["user:read"])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["user:read"])]
    private ?\DateTimeImmutable $birthdate = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["user:read"])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: CompanyUser::class,)]
    private Collection $companies;


    public function __construct()
    {
        $this->companies = new ArrayCollection();
    }

    public static function createFromPayload($id, array $payload): JWTUserInterface
    {
        $user = new self;

        //$user->setId(is_string($id) ? new Uuid($id) : $id);
        $user->setId( $id);
        //$user->setUsername($payload['username'] ?? '');
        $user->setEmail($payload['username'] ?? '');
        $user->setRoles($payload['roles']);

        return $user;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @return list<string>
     * @see UserInterface
     *
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }


    public function getIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    #[Groups(["user:read", "service:read","review:read","reservation:read","company_user:read"])]
    public function getFullName(): string
    {
        return  $this->firstName ." ".$this->lastName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getAvatar(): ?MediaObject
    {
        return $this->avatar;
    }

    public function setAvatar(?MediaObject $avatar): static
    {
        $this->avatar = $avatar;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    #[Groups(["user:read"])]
    public function getCreatedAtAgo(): string
    {
        if ($this->createdAt === null) {
            return "";
        }
        return Carbon::instance($this->createdAt)->diffForHumans();
    }

    #[Groups(["user:read"])]
    public function getUpdatedAtAgo(): string
    {
        if ($this->updatedAt === null) {
            return "";
        }
        return Carbon::instance($this->updatedAt)->diffForHumans();
    }

    /**
     * @return Collection<int, CompanyUser>
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(CompanyUser $companyUser): static
    {
        if (!$this->companies->contains($companyUser)) {
            $this->companies->add($companyUser);
            $companyUser->setUser($this);
        }

        return $this;
    }

    public function removeCompany(CompanyUser $companyUser): static
    {
        if ($this->companies->removeElement($companyUser)) {
            // set the owning side to null (unless already changed)
            if ($companyUser->getUser() === $this) {
                $companyUser->setUser(null);
            }
        }

        return $this;
    }

    public function getBirthdate(): ?\DateTimeImmutable
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeImmutable $birthdate): static
    {
        $this->birthdate = $birthdate;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

}
