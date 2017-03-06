<?php

namespace AppBundle\Security;

use AppBundle\Entity\BiologicalOriginCategory;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BiologicalOriginCategoryVoter extends Voter
{
    const EDIT = 'CATEGORY_EDIT';
    const DELETE = 'CATEGORY_DELETE';

    protected function supports($attribute, $subject)
    {
        // If the attribute isn't one we support, return false
        if (!in_array($attribute, [self::EDIT, self::DELETE])) {
            return false;
        }

        // Only vote for BiologicalOriginCategory object
        if (!$subject instanceof BiologicalOriginCategory) {
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
        $category = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($category, $user);
            case self::DELETE:
                return $this->canDelete($category, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canEdit(BiologicalOriginCategory $category, User $user)
    {
        if ($this->canDelete($category, $user)) {
            return true;
        }

        return false;
    }

    private function canDelete(BiologicalOriginCategory $category, User $user)
    {
        if ($category->getTeam()->isMember($user)) {
            return true;
        }

        return false;
    }
}
