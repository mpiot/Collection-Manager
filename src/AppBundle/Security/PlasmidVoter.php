<?php

namespace AppBundle\Security;

use AppBundle\Entity\Plasmid;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PlasmidVoter extends Voter
{
    const VIEW = 'PLASMID_VIEW';
    const EDIT = 'PLASMID_EDIT';
    const DELETE = 'PLASMID_DELETE';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        // If the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])) {
            return false;
        }

        // Only vote for Project object
        if (!$subject instanceof Plasmid) {
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

        // If user is a SuperAdmin user
        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        // In all other case
        $plasmid = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($plasmid, $user, $token);
            case self::EDIT:
                return $this->canEdit($plasmid, $user);
            case self::DELETE:
                return $this->canDelete($plasmid, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Plasmid $plasmid, User $user, TokenInterface $token)
    {
        if ($this->canEdit($plasmid, $user)) {
            return true;
        }

        // If the user can view the strain,he can view the plasmid
        foreach ($plasmid->getStrains() as $strain) {
            if ($this->decisionManager->decide($token, ['STRAIN_VIEW'], $strain)) {
                return true;
            }
        }

        return false;
    }

    private function canEdit(Plasmid $plasmid, User $user)
    {
        if ($this->canDelete($plasmid, $user)) {
            return true;
        }

        return false;
    }

    private function canDelete(Plasmid $plasmid, User $user)
    {
        if ($plasmid->getTeam()->isMember($user)) {
            return true;
        }

        return false;
    }
}
