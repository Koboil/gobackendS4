<?php

namespace App\ApiResource;

use ApiPlatform\Action\NotFoundAction;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\State\VerifyProcessor;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(
            controller: NotFoundAction::class,
            output: false,
            read: false
        ),
        new Post(
            uriTemplate: '/verifies/resend',
            openapiContext: [
                'summary' => 'Resend Confirmation email',
                'description' => 'Resend confirmation email',
                'responses' => [
                    '200' => [
                        'description' => 'Statistics collection retrieved successfully.',
                    ],
                ]
            ]
        ),
    ],
    normalizationContext: ['groups' => ['verify:read']],
    denormalizationContext: ['groups' => ['verify:write']],
    paginationEnabled: false,
    processor: VerifyProcessor::class,
)]
#[ORM\HasLifecycleCallbacks]
class Verify
{
    #[Groups(["statistic:read"])]
    public ?\DateTimeInterface $date = null;

    #[Groups(["statistic:read", 'verify:write'])]
    public ?int $userId = null;

    public function __construct()
    {
        $this->date = new \DateTime();
    }

    #[ApiProperty(identifier: true)]
    #[Groups(["statistic:read"])]
    public function getDateAgo(): string
    {
        if ($this->date === null) {
            return "";
        }
        return Carbon::instance($this->date)->diffForHumans();
    }
}
