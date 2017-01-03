<?php

namespace AppBundle\Security;

use AppBundle\Entity\Tube;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TubeVoter extends Voter
{
    const VIEW = 'TUBE_VIEW';
    const RESTORE = 'TUBE_RESTORE';
    const DELETE = 'TUBE_DELETE';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        // If the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::RESTORE, self::DELETE])) {
            return false;
        }

        // Only vote for Strain object
        if (!$subject instanceof Tube) {
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
        $tube = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($tube, $user);
            case self::RESTORE:
                return $this->canRestore($tube, $user);
            case self::DELETE:
                return $this->canDelete($tube, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Tube $tube, User $user)
    {
        // If the user can restore, he can view
        if ($this->canRestore($tube, $user)) {
            return true;
        }

        // If the user is member of the project
        if ($tube->getProject()->isMember($user)) {
            return true;
        }

        return false;
    }

    private function canRestore(Tube $tube, User $user)
    {
        // If the user can delete, he can view
        if ($this->canDelete($tube, $user)) {
            return true;
        }

        return false;
    }

    private function canDelete(Tube $tube, User $user)
    {
        // If the user is a project administrator of the concerned tube
        $tubeProject = $tube->getProject();

        if ($user->isProjectAdministratorOf($tubeProject)) {
            return true;
        }

        // If the user is an administrator of a team concerned by the strain
        $tubeTeam = $tubeProject->getTeam();
        $userAdministeredTeams = $user->getAdministeredTeams()->toArray();

        if (in_array($tubeTeam, $userAdministeredTeams)) {
            return true;
        }

        return false;
    }
}
