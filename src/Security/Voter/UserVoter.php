<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';

    public function __construct(private Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        if (!($subject instanceof User)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // ROLE_ADMIN can do anything!
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $currentUser = $token->getUser();

        if (!$currentUser instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        /** @var User $user */
        $user = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($user, $currentUser);
            case self::EDIT:
                return $this->canEdit($user, $currentUser);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(User $data, User $user): bool
    {
        // if they can edit, they can view
        if ($this->canEdit($data, $user)) {
            return true;
        }

        if ($this->security->isGranted('ROLE_ADMIN_OPERATOR')) {
            return true;
        }

        return false;
    }

    private function canEdit(User $data, User $currentUser): bool
    {
        // User can edit itself
        if ($data->getEmail() == $currentUser->getUserIdentifier() || $data->getId() == $currentUser->getId()) {
            return true;
        }

        //ROLE_ADMIN_OPERATOR can create, edit and delete users (not with role ROLE_ADMIN)
        if (in_array("ROLE_ADMIN", $data->getRoles()) === true) {
            return true;
        }

        return false;
    }
}
