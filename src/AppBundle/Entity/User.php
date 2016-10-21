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
        $this->teamRequests = new ArrayCollection();
        $this->gmoStrains = new ArrayCollection();
        $this->wildStrains = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->administeredProjects = new ArrayCollection();
    }

    /**
     * Add administered team.
     *
     * @param Team $team
     */
    public function addAdministeredTeam(Team $team)
    {
        $this->administeredTeams->add($team);
    }

    /**
     * @return ArrayCollection
     */
    public function getAdministeredTeams()
    {
        return $this->administeredTeams;
    }

    /**
     * Is a team administrator ?
     *
     * @return bool
     */
    public function isTeamAdministrator()
    {
        return !$this->administeredTeams->isEmpty();
    }

    /**
     * Is an administrator of this team ?
     *
     * @param Team $team
     * @return bool
     */
    public function isAdministratorOf(Team $team)
    {
        return $this->administeredTeams->contains($team);
    }

    /**
     * Add team.
     *
     * @param Team $team
     */
    public function addTeam(Team $team)
    {
        $this->teams->add($team);
    }

    /**
     * Get teams.
     *
     * @return ArrayCollection
     */
    public function getTeams()
    {
        return $this->teams;
    }

    /**
     * Get teams Ids.
     *
     * @return array
     */
    public function getTeamsId()
    {
        $teamsId = [];

        foreach ($this->teams as $team) {
            $teamsId[] = $team->getId();
        }

        return $teamsId;
    }

    /**
     * Is in a team ?
     *
     * @return bool
     */
    public function isInTeam()
    {
        return !$this->teams->isEmpty();
    }

    /**
     * Is in this teams ?
     *
     * @param Team $team
     *
     * @return bool
     */
    public function hasTeam(Team $team)
    {
        return $this->teams->contains($team);
    }

    /**
     * Get team requests.
     *
     * @return ArrayCollection
     */
    public function getTeamRequests()
    {
        return $this->teamRequests;
    }

    /**
     * Has requested for this team ?
     *
     * @param Team $team
     *
     * @return bool
     */
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

    /**
     * Add a project.
     *
     * @param Project $project
     */
    public function addProject(Project $project)
    {
        $this->projects->add($project);
    }

    /**
     * Get projects.
     *
     * @return ArrayCollection
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Add an administered project.
     *
     * @param Project $project
     */
    public function addAdministeredProject(Project $project)
    {
        $this->administeredProjects->add($project);
    }

    /**
     * Get administered projects.
     *
     * @return ArrayCollection
     */
    public function getAdministeredProjects()
    {
        return $this->administeredProjects;
    }

    /**
     * Is a project administrator ?
     *
     * @return bool
     */
    public function isProjectAdministrator()
    {
        return !$this->administeredProjects->isEmpty();
    }

    /**
     * Is an administrator of this project ?
     *
     * @param Team $team
     * @return bool
     */
    public function isProjectAdministratorOf(Project $project)
    {
        return $this->administeredProjects->contains($project);
    }
}
