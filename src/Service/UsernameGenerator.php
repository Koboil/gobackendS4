<?php

namespace App\Service;

use App\Repository\UserRepository;
use Symfony\Component\String\Slugger\AsciiSlugger;

class UsernameGenerator
{

    public function __construct(private readonly UserRepository $userRepository)
    {
     }

    public function generateUsername(string $firstName, string $lastName): string
    {
        $slugger = new AsciiSlugger();
        $baseUsername = $slugger->slug(strtolower($firstName . '.' . $lastName))->toString();

        $username = $baseUsername;
        $i = 1;

        // Ensure the username is unique
        while ($this->userRepository->findOneBy(['username' => $username])) {
            $username = $baseUsername . $i;
            $i++;
        }
        return $username;
    }
}

