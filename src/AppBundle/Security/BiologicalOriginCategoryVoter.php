<?php

namespace AppBundle\Security;

use AppBundle\Entity\BiologicalOriginCategory;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BiologicalOriginCategoryVoter extends Voter
{
    const EDIT = 'CATEGORY_EDIT';
    const DELETE = 'CATEGORY_DELETE';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

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

        // If user is a SuperAdmin user
        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
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

        if ($category->getTeam()->isMember($user)) {
            return true;
        }

        return false;
    }

    private function canDelete(BiologicalOriginCategory $category, User $user)
    {
        // team administrators can delete it
        if ($category->getTeam()->isAdministrator($user)) {
            return true;
        }

        return false;
    }
}
