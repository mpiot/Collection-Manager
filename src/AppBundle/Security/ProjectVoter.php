<?php

namespace AppBundle\Security;

use AppBundle\Entity\Project;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ProjectVoter extends Voter
{
    const VIEW = 'PROJECT_VIEW';
    const EDIT = 'PROJECT_EDIT';
    const DELETE = 'PROJECT_DELETE';

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

        // Only vote for Project object
        if (!$subject instanceof Project) {
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
        $project = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($project, $user);
            case self::EDIT:
                return $this->canEdit($project, $user);
            case self::DELETE:
                return $this->canDelete($project, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Project $project, User $user)
    {
        if ($this->canEdit($project, $user)) {
            return true;
        }

        $projectTeams = $project->getTeams()->toArray();
        $userTeams = $user->getTeams()->toArray();

        if (!empty(array_intersect($projectTeams, $userTeams))) {
            return true;
        }

        return false;
    }

    private function canEdit(Project $project, User $user)
    {
        if ($this->canDelete($project, $user)) {
            return true;
        }

        $projectTeams = $project->getTeams()->toArray();
        $userModeratedTeams = $user->getModeratedTeams()->toArray();

        if (!empty(array_intersect($projectTeams, $userModeratedTeams))) {
            return true;
        }

        return false;
    }

    private function canDelete(Project $project, User $user)
    {


        $projectTeams = $project->getTeams()->toArray();
        $userAdministeredTeams = $user->getAdministeredTeams()->toArray();

        if (!empty(array_intersect($projectTeams, $userAdministeredTeams))) {
            return true;
        }

        return false;
    }
}
