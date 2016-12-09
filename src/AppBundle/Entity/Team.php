<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Team.
 *
 * @ORM\Table(name="team")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TeamRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Team
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", inversedBy="administeredTeams")
     * @ORM\JoinTable(name="team_administrators")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\Count(min="1", minMessage = "You must specify at least one administrator.")
     */
    private $administrators;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", inversedBy="teams")
     * @ORM\JoinTable(name="team_members")
     * @ORM\JoinColumn(nullable=false)
     */
    private $members;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Project", mappedBy="teams")
     */
    private $projects;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\TeamRequest", mappedBy="team")
     */
    private $teamRequests;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Plasmid", mappedBy="team")
     */
    private $plasmids;

    /**
     * @ORM\Column(name="last_plasmid_number", type="integer", nullable=false)
     */
    private $lastPlasmidNumber;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Primer", mappedBy="team")
     */
    private $primers;

    /**
     * @ORM\Column(name="last_primer_number", type="integer", nullable=false)
     */
    private $lastPrimerNumber;

    /**
     * Team constructor.
     */
    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->administrators = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->teamRequests = new ArrayCollection();
        $this->plasmids = new ArrayCollection();
        $this->lastPlasmidNumber = 0;
        $this->primers = new ArrayCollection();
        $this->lastPrimerNumber = 0;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Team
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add administrator.
     *
     * @param User $user
     *
     * @return Team
     */
    public function addAdministrator(User $user)
    {
        $user->addAdministeredTeam($this);
        $this->administrators->add($user);

        // When you add a user as an administrator, add it as a member too
        $this->addMember($user);

        return $this;
    }

    /**
     * Remove administrator.
     *
     * @param User $user
     */
    public function removeAdministrator(User $user)
    {
        $this->administrators->removeElement($user);
    }

    /**
     * Get administrators.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdministrators()
    {
        return $this->administrators;
    }

    /**
     * Is User administrator ?
     *
     * @param User $user
     *
     * @return bool
     */
    public function isAdministrator(User $user)
    {
        return $this->administrators->contains($user);
    }

    /**
     * Add member.
     *
     * @param User $user
     *
     * @return Team
     */
    public function addMember(User $user)
    {
        $user->addTeam($this);
        $this->members->add($user);

        return $this;
    }

    /**
     * Remove member.
     *
     * @param User $user
     */
    public function removeMember(User $user)
    {
        $this->members->removeElement($user);
    }

    /**
     * Get members.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Is User member ?
     *
     * @param User $user
     *
     * @return bool
     */
    public function isMember(User $user)
    {
        return $this->members->contains($user);
    }

    /**
     * Add project.
     *
     * @param Project $project
     *
     * @return Team
     */
    public function addProject(Project $project)
    {
        $this->projects->add($project);

        return $this;
    }

    /**
     * Remove project.
     *
     * @param Project $project
     */
    public function removeProject(Project $project)
    {
        $this->projects->removeElement($project);
    }

    /**
     * Get projects.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProjects()
    {
        return $this->projects;
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


    public function getTeamRequestFor(User $user)
    {
        foreach($this->teamRequests as $teamRequest) {
            if ($user === $teamRequest->getUser()) {
                return $teamRequest;
            }
        }

        return null;
    }

    /**
     * Get plasmids.
     *
     * @return ArrayCollection
     */
    public function getPlasmids()
    {
        return $this->plasmids;
    }

    /**
     * Set last plasmid number.
     *
     * @param int $number
     * @return $this
     */
    public function setLastPlasmidNumber(int $number)
    {
        $this->lastPlasmidNumber = $number;

        return $this;
    }

    /**
     * Get last plasmid number.
     *
     * @return int
     */
    public function getLastPlasmidNumber()
    {
        return $this->lastPlasmidNumber;
    }

    /**
     * Get primers.
     *
     * @return ArrayCollection
     */
    public function getPrimers()
    {
        return $this->primers;
    }

    /**
     * Set last primer number.
     *
     * @param int $number
     * @return $this
     */
    public function setLastPrimerNumber(int $number)
    {
        $this->lastPrimerNumber = $number;

        return $this;
    }

    /**
     * Get last primer number.
     *
     * @return int
     */
    public function getLastPrimerNumber()
    {
        return $this->lastPrimerNumber;
    }
}
