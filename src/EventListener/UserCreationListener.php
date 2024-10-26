<?php

namespace App\EventListener;

use App\Entity\User;
use App\Service\UsernameGenerator;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: User::class)]
final class UserCreationListener
{
    public function __construct(private readonly UsernameGenerator $usernameGenerator)
    {
    }

    public function prePersist(User $user, PrePersistEventArgs $event): void
    {
         if (empty($user->getUsername())) {
            $username = $this->usernameGenerator->generateUsername($user->getFirstName(), $user->getLastName());
            $user->setUsername($username);
        }
    }
}
