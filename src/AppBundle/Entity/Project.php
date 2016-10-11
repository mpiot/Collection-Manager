<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Project
 *
 * @ORM\Table(name="project")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProjectRepository")
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
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="prefix", type="string", length=255, unique=true)
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
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Team", inversedBy="projects")
     * @ORM\JoinTable(name="projects_teams")
     * @ORM\JoinColumn(nullable=true)
     */
    private $teams;

    /**
     * Project constructor.
     */
    public function __construct()
    {
        $this->boxes = new ArrayCollection();
        $this->teams = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set prefix
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
     * Get prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set description
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
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param Box $box
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
     * @return Boxes|ArrayCollection
     */
    public function getBoxes()
    {
        return $this->boxes;
    }

    /**
     * Add team
     *
     * @param Team $team
     *
     * @return Project
     */
    public function addTeam(Team $team)
    {
        $team->addProject($this);
        $this->teams->add($team);

        return $this;
    }

    /**
     * Remove team
     *
     * @param Team $team
     */
    public function removeTeam(Team $team)
    {
        $this->teams->removeElement($team);
    }

    /**
     * Get team
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTeams()
    {
        return $this->teams;
    }
}

