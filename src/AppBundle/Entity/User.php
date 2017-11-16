<?php

// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @UniqueEntity("email")
 */
class User implements AdvancedUserInterface, \Serializable
{
    const ROLE_DEFAULT = 'ROLE_USER';
    const NUM_ITEMS = 10;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     * @Assert\Regex(
     *     pattern="/.+@inra.fr/",
     *     message="You must use an @inra.fr email."
     * )
     */
    private $email;

    /**
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * @ORM\Column(name="password", type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(name="roles", type="array")
     */
    private $roles;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(name="confirmation_token", type="string", nullable=true)
     */
    private $confirmationToken;

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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Strain", mappedBy="createdBy")
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
        $this->roles = [];
        $this->isActive = false;
        $this->teams = new ArrayCollection();
        $this->administeredTeams = new ArrayCollection();
        $this->strains = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->administeredProjects = new ArrayCollection();
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email.
     *
     * @param $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * Set plain password.
     *
     * @param $password
     */
    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    /**
     * Get plain password.
     *
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Set password.
     *
     * @param $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Get salt.
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Set roles.
     *
     * @param $array
     *
     * @return $this
     */
    public function setRoles($array)
    {
        $this->roles = $array;

        return $this;
    }

    /**
     * Add role.
     *
     * @param $role
     *
     * @return $this
     */
    public function addRole($role)
    {
        $role = strtoupper($role);
        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * Remove role.
     *
     * @param $role
     *
     * @return $this
     */
    public function removeRole($role)
    {
        if (false !== $key = array_search($role, $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * Get roles.
     *
     * @return array
     */
    public function getRoles()
    {
        $roles = $this->roles;
        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
    }

    /**
     * Set isActive.
     *
     * @param bool $isActive
     *
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive.
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set confirmation token.
     *
     * @param string $confirmationToken
     *
     * @return User
     */
    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    /**
     * Get confirmation token.
     *
     * @return string
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * Erase credentials.
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    /**
     * Is account non expired ?
     *
     * @return bool
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * Is account non locked ?
     *
     * @return bool
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * Is credential non expired ?
     *
     * @return bool
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Is enabled ?
     *
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->isActive;
    }

    /**
     * Serialize.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->email,
            $this->password,
            $this->isActive,
        ]);
    }

    /**
     * Unserialize.
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->email,
            $this->password,
            $this->isActive) = unserialize($serialized);
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

    /**
     * Get fullName.
     *
     * @return string
     */
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
        foreach ($this->administeredProjects as $administeredProject) {
            if ($administeredProject->isValid()) {
                return true;
            }
        }

        return false;
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
