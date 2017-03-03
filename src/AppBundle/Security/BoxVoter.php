<?php

namespace AppBundle\Security;

use AppBundle\Entity\Box;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BoxVoter extends Voter
{
    const VIEW = 'BOX_VIEW';
    const EDIT = 'BOX_EDIT';
    const DELETE = 'BOX_DELETE';

    protected function supports($attribute, $subject)
    {
        // If the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])) {
            return false;
        }

        // Only vote for Project object
        if (!$subject instanceof Box) {
            return false;
        }

        // Else it's a supported attribute and a supported object
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        // If user is not log in, deny access
        if (!$user instanceof User) {
            return false;
        }

        // In all other case
        $box = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($box, $user);
            case self::EDIT:
                return $this->canEdit($box, $user);
            case self::DELETE:
                return $this->canDelete($box, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Box $box, User $user)
    {
        if ($box->getProject()->isMember($user)) {
            return true;
        }

        if ($this->canEdit($box, $user)) {
            return true;
        }

        return false;
    }

    private function canEdit(Box $box, User $user)
    {
        if ($this->canDelete($box, $user)) {
            return true;
        }

        return false;
    }

    private function canDelete(Box $box, User $user)
    {
        // The Project administrator can delete a box
        if ($box->getProject()->isAdministrator($user)) {
            return true;
        }

        // A team administrator of the project can delete it
        if ($box->getProject()->getTeam()->isAdministrator($user)) {
            return true;
        }

        return false;
    }
}
