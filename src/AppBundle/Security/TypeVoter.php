<?php

namespace AppBundle\Security;

use AppBundle\Entity\Type;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TypeVoter extends Voter
{
    const EDIT = 'TYPE_EDIT';
    const DELETE = 'TYPE_DELETE';

    protected function supports($attribute, $subject)
    {
        // If the attribute isn't one we support, return false
        if (!in_array($attribute, [self::EDIT, self::DELETE])) {
            return false;
        }

        // Only vote for BiologicalOriginCategory object
        if (!$subject instanceof Type) {
            return false;
        }

        // Else it's a supported attribute and a supported object
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        // If user is not log out, deny access
        if (!$user instanceof User) {
            return false;
        }

        // In all other case
        $type = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($type, $user);
            case self::DELETE:
                return $this->canDelete($type, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canEdit(Type $type, User $user)
    {
        if ($this->canDelete($type, $user)) {
            return true;
        }

        return false;
    }

    private function canDelete(Type $type, User $user)
    {
        if ($type->getTeam()->isMember($user)) {
            return true;
        }

        return false;
    }
}
