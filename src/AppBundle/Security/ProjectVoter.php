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
    const VALIDATE = 'PROJECT_VALIDATE';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        // If the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::VALIDATE])) {
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

        // In all other case
        $project = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($project, $user);
            case self::EDIT:
                return $this->canEdit($project, $user);
            case self::DELETE:
                return $this->canDelete($project, $user);
            case self::VALIDATE:
                return $this->canValidate($project, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Project $project, User $user)
    {
        if (!$project->isPrivate()) {
            return true;
        }

        if ($project->isMember($user)) {
            return true;
        }

        if ($this->canDelete($project, $user)) {
            return true;
        }

        return false;
    }

    private function canEdit(Project $project, User $user)
    {
        if ($project->isAdministrator($user)) {
            return true;
        }

        if ($this->canDelete($project, $user)) {
            return true;
        }

        return false;
    }

    private function canDelete(Project $project, User $user)
    {
        // Only the team administrators of the project can delete it
        if ($project->getTeam()->isAdministrator($user)) {
            return true;
        }

        return false;
    }

    private function canValidate(Project $project, User $user)
    {
        // Only the team administrators of the project can validate it
        if ($project->getTeam()->isAdministrator($user)) {
            return true;
        }

        return false;
    }
}
