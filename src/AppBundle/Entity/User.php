<?php

// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @ORM\Column(name="first_name", type="string", length=255)
     * @Assert\NotBlank(message="Please enter your first name.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     minMessage="The name is too short.",
     *     maxMessage="The name is too long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    private $firstName;

    /**
     * @ORM\Column(name="last_name", type="string", length=255)
     * @Assert\NotBlank(message="Please enter your last name.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     minMessage="The name is too short.",
     *     maxMessage="The name is too long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    private $lastName;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Team", mappedBy="administrators")
     */
    private $administeredTeams;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Team", mappedBy="members")
     */
    private $teams;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Team")
     */
    private $favoriteTeam;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\TeamRequest", mappedBy="user")
     */
    private $teamRequests;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Strain", mappedBy="author")
     */
    private $strains;

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
        $this->strains = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->administeredProjects = new ArrayCollection();
    }

    /**
     * Set firstName.
     *
     * @param $firstName
     *
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName.
     *
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName.
     *
     * @param $firstName
     *
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName.
     *
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    public function getFullName()
    {
        return $this->firstName.' '.$this->lastName;
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
     * Get administered teams Ids.
     *
     * @return array
     */
    public function getAdministeredTeamsId()
    {
        $administeredTeamsId = [];

        foreach ($this->administeredTeams as $administeredTeam) {
            $administeredTeamsId[] = $administeredTeam->getId();
        }

        return $administeredTeamsId;
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
     *
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
     * Set favorite team.
     *
     * @param Team $team
     *
     * @return $this
     */
    public function setFavoriteTeam(Team $team)
    {
        $this->favoriteTeam = $team;

        return $this;
    }

    /**
     * Get favorite team.
     *
     * @return Team
     */
    public function getFavoriteTeam()
    {
        // If the user have no set a favorite Team, the first match team is the favorite
        if (null === $this->favoriteTeam) {
            return $this->teams->first();
        }

        return $this->favoriteTeam;
    }

    /**
     * Is favorite team ?
     *
     * @param Team $team
     *
     * @return bool
     */
    public function isFavoriteTeam(Team $team)
    {
        return $team === $this->getFavoriteTeam();
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

        foreach ($this->teamRequests as $teamRequest) {
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
     * Get projects Ids.
     *
     * @return array
     */
    public function getProjectsId()
    {
        $projectsId = [];

        foreach ($this->projects as $project) {
            $projectsId[] = $project->getId();
        }

        return $projectsId;
    }

    /**
     * Is a project member ?
     *
     * @return bool
     */
    public function isProjectMember()
    {
        return !$this->projects->isEmpty();
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
     *
     * @return bool
     */
    public function isProjectAdministratorOf(Project $project)
    {
        return $this->administeredProjects->contains($project);
    }

    /**
     * Get strains.
     *
     * @return ArrayCollection
     */
    public function getStrains()
    {
        return $this->strains;
    }
}
