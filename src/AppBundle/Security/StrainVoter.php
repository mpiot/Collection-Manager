<?php

namespace AppBundle\Security;

use AppBundle\Entity\GmoStrain;
use AppBundle\Entity\Strain;
use AppBundle\Entity\User;
use AppBundle\Entity\WildStrain;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class StrainVoter extends Voter
{
    const VIEW = 'STRAIN_VIEW';
    const EDIT = 'STRAIN_EDIT';
    const DELETE = 'STRAIN_DELETE';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        // If the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::DELETE))) {
            return false;
        }

        // Only vote for Strain object
        if (!$subject instanceof GmoStrain && !$subject instanceof WildStrain) {
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
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }

        // In all other case
        $strain = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($strain, $user);
            case self::EDIT:
                return $this->canEdit($strain, $user);
            case self::DELETE:
                return $this->canDelete($strain, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Strain $strain, User $user)
    {
        // If the user can edit, he can view
        if ($this->canEdit($strain, $user)) {
            return true;
        }

        // If the user is member of the project
        if (in_array($user, $strain->getAuthorizedTeams())) {
            return true;
        }

        return false;
    }

    private function canEdit(Strain $strain, User $user)
    {
        // If the user can delete, he can view
        if ($this->canDelete($strain, $user)) {
            return true;
        }

        return false;
    }

    private function canDelete(Strain $strain, User $user)
    {
        // If the user is the author
        if ($strain->isAuthor($user)) {
            return true;
        }

        // If the user is an administrator of project concern by the strain
        $strainTeams = $strain->getTeams();
        $userAdministeredTeams = $user->getAdministeredTeams()->toArray();

        if (!empty(array_intersect($strainTeams, $userAdministeredTeams))) {
            return true;
        }

        return false;
    }
}
