<?php


namespace App\Event;

use App\Entity\User;

class UserEvent
{
    const CONFIRM_EMAIL = 'UserEvent.confirmEmail';
    const SEND_PLAIN_PASSWORD = 'UserEvent.sendPlainPassword';


    public function __construct(private User $user, private ?string $plainPassword = null)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }


    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }
}
