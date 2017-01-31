<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Project.
 *
 * @ORM\Table(name="project")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProjectRepository")
 * @UniqueEntity({"name", "team"}, message="This name is already used by another project.")
 * @UniqueEntity({"prefix", "team"}, message="This prefix is already used by another project.")
 */
class Project
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="prefix", type="string", length=255)
     */
    private $prefix;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Box", mappedBy="project", cascade={"remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $boxes;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Team", inversedBy="projects")
     * @ORM\JoinColumn(nullable=false)
     */
    private $team;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", inversedBy="administeredProjects")
     * @ORM\JoinTable(name="project_administrators")
     * @ORM\JoinColumn(nullable=true)
     */
    private $administrators;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", inversedBy="projects")
     * @ORM\JoinTable(name="project_members")
     * @ORM\JoinColumn(nullable=true)
     */
    private $members;

    /**
     * Project constructor.
     */
    public function __construct()
    {
        $this->boxes = new ArrayCollection();
        $this->administrators = new ArrayCollection();
        $this->members = new ArrayCollection();
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
     * @return Project
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
     * Set prefix.
     *
     * @param string $prefix
     *
     * @return Project
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Project
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param Box $box
     *
     * @return $this
     */
    public function addBox(Box $box)
    {
        if (!$this->boxes->contains($box)) {
            $this->boxes[] = $box;
            $box->setProject($this);
        }

        return $this;
    }

    /**
     * @param $box
     *
     * @return $this
     */
    public function removeBox($box)
    {
        if ($this->boxes->contains($box)) {
            $this->boxes->removeElement($box);
        }

        return $this;
    }

    /**
     * @return Box|ArrayCollection
     */
    public function getBoxes()
    {
        return $this->boxes;
    }

    /**
     * Set team.
     *
     * @param Team $team
     *
     * @return Project
     */
    public function setTeam(Team $team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team.
     *
     * @return Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Add administrator.
     *
     * @param User $user
     *
     * @return Project
     */
    public function addAdministrator(User $user)
    {
        $user->addAdministeredProject($this);
        $this->administrators->add($user);

        return $this;
    }

    /**
     * Remove administrator.
     *
     * @param Team $team
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
     * Is administrator ?
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
     * @return Project
     */
    public function addMember(User $user)
    {
        $user->addProject($this);
        $this->members->add($user);

        return $this;
    }

    /**
     * Remove member.
     *
     * @param Team $team
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
     * Get members id.
     *
     * @return array
     */
    public function getMembersId()
    {
        $membersId = [];

        foreach ($this->members as $member) {
            $membersId[] = $member->getId();
        }

        return $membersId;
    }

    /**
     * Is member ?
     *
     * @param User $user
     *
     * @return bool
     */
    public function isMember(User $user)
    {
        return $this->members->contains($user);
    }
}
