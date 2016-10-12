<?php
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Team", mappedBy="administrators")
     */
    protected $administeredTeams;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Team", mappedBy="moderators")
     */
    protected $moderatedTeams;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Team", mappedBy="members")
     */
    protected $teams;

    public function __construct()
    {
        parent::__construct();
        $this->teams = new ArrayCollection();
        $this->administeredTeams = new ArrayCollection();
        $this->moderatedTeams = new ArrayCollection();
    }

    public function addAdministeredTeam(Team $team)
    {
        $this->administeredTeams->add($team);
    }

    public function removeAdministeredTeam(Team $team)
    {
        $this->administeredTeams->removeElement($team);
    }

    public function getAdministeredTeams()
    {
        return $this->administeredTeams;
    }

    public function addModeratedTeam(Team $team)
    {
        $this->moderatedTeams->add($team);
    }

    public function removeModeratedTeam(Team $team)
    {
        $this->moderatedTeams->removeElement($team);
    }

    public function getModeratedTeams()
    {
        return $this->moderatedTeams;
    }

    public function addTeam(Team $team)
    {
        $this->teams->add($team);
    }

    public function removeTeam(Team $team)
    {
        $this->teams->removeElement($team);
    }

    public function getTeams()
    {
        return $this->teams;
    }

    public function getTeamsId()
    {
        $teamsId = [];

        foreach ($this->teams as $team) {
            $teamsId[] = $team->getId();
        }

        return $teamsId;
    }

    public function isTeamAdministrator()
    {
        return !$this->administeredTeams->isEmpty();
    }

    public function isTeamModerator()
    {
        return !$this->moderatedTeams->isEmpty();
    }

    public function isTeamAministratorOrModerator()
    {
        return $this->isTeamAdministrator() || $this->isTeamModerator();
    }

    public function isAdministratorOf(Team $team)
    {
        return $this->administeredTeams->contains($team);
    }

    public function isModeratorOf(Team $team)
    {
        return $this->moderatedTeams->contains($team);
    }

    public function isAministratorOrModeratorOf(Team $team)
    {
        return $this->isAdministratorOf($team) || $this->isModeratorOf($team);
    }

    public function isInTeam()
    {
        return !$this->teams->isEmpty();
    }
}
