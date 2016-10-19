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
    private $administeredTeams;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Team", mappedBy="moderators")
     */
    private $moderatedTeams;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Team", mappedBy="members")
     */
    private $teams;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\TeamRequest", mappedBy="user")
     */
    private $teamRequests;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\GmoStrain", mappedBy="author")
     */
    private $gmoStrains;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\WildStrain", mappedBy="author")
     */
    private $wildStrains;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Project", mappedBy="administrators")
     */
    private $administeredProjects;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Project", mappedBy="members")
     */
    private $projects;

    public function __construct()
    {
        parent::__construct();
        $this->teams = new ArrayCollection();
        $this->administeredTeams = new ArrayCollection();
        $this->moderatedTeams = new ArrayCollection();
        $this->teamRequests = new ArrayCollection();
        $this->gmoStrains = new ArrayCollection();
        $this->wildStrains = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->administeredProjects = new ArrayCollection();
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

    public function hasTeam(Team $team)
    {
        return $this->teams->contains($team);
    }

    public function getTeamRequests()
    {
        return $this->teamRequests;
    }

    public function hasRequestedTeam(Team $team)
    {
        $result = false;

        foreach ($this->teamRequests as $teamRequest)
        {
            if ($team === $teamRequest->getTeam()) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    public function addProject(Project $project)
    {
        $this->projects->add($project);
    }

    public function removeProject(Project $project)
    {
        $this->projects->removeElement($project);
    }

    public function getProjects()
    {
        return $this->projects;
    }

    public function addAdministeredProject(Project $project)
    {
        $this->administeredProjects->add($project);
    }

    public function removeAdministeredProject(Project $project)
    {
        $this->administeredProjects->removeElement($project);
    }

    public function getAdministeredProjects()
    {
        return $this->administeredProjects;
    }

    public function getUsernameAndTeams()
    {
        return $this->getUsername().' - Teams('.join(',', $this->getTeamsId()).')';
    }
}
