<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
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
    const NUM_ITEMS = 10;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(name="slug", type="string", length=128, unique=true)
     */
    private $slug;

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
     * @var Team
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
     * @ORM\Column(name="private", type="boolean")
     */
    private $private;

    /**
     * @ORM\Column(name="valid", type="boolean")
     */
    private $valid = false;

    /**
     * @ORM\Column(name="last_strain_number", type="integer", nullable=false)
     */
    private $lastStrainNumber = 0;

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
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
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
        $this->prefix = strtoupper($prefix);

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

    /**
     * Set private.
     *
     * @param bool $private
     *
     * @return Project
     */
    public function setPrivate($private)
    {
        $this->private = $private;

        return $this;
    }

    /**
     * Get private.
     *
     * @return bool
     */
    public function getPrivate()
    {
        return $this->private;
    }

    /**
     * Is private ?
     *
     * @return bool
     */
    public function isPrivate()
    {
        return $this->getPrivate();
    }

    /**
     * Set valid.
     *
     * @param bool $valid
     *
     * @return Project
     */
    public function setValid($valid)
    {
        $this->valid = $valid;

        return $this;
    }

    /**
     * Get valid.
     *
     * @return bool
     */
    public function getValid()
    {
        return $this->valid;
    }

    /**
     * Is valid ?
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * Set last strain number.
     *
     * @param int $number
     *
     * @return $this
     */
    public function setLastStrainNumber(int $number)
    {
        $this->lastStrainNumber = $number;

        return $this;
    }

    /**
     * Get last strain number.
     *
     * @return int
     */
    public function getLastStrainNumber()
    {
        return $this->lastStrainNumber;
    }
}
