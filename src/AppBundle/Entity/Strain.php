<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Strain.
 *
 * @ORM\MappedSuperclass()
 * @ORM\HasLifecycleCallbacks()
 */
class Strain
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deletionDate", type="datetime", nullable=true)
     */
    private $deletionDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="deleted", type="boolean")
     */
    private $deleted;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="auto_name", type="string", length=255, unique=true)
     */
    private $autoName;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="sequenced", type="boolean")
     */
    private $sequenced;

    public function __construct()
    {
        $this->creationDate = new \DateTime();
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return Strain
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate.
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set deletionDate.
     *
     * @param \DateTime $deletionDate
     *
     * @return Strain
     */
    public function setDeletionDate($deletionDate)
    {
        $this->deletionDate = $deletionDate;

        return $this;
    }

    /**
     * Get deletionDate.
     *
     * @return \DateTime
     */
    public function getDeletionDate()
    {
        return $this->deletionDate;
    }

    /**
     * Set deleted.
     *
     * @param bool $deleted
     *
     * @return Strain
     */
    public function setDeleted(bool $deleted)
    {
        if (true === $deleted && false === $this->deleted) {
            $this->deletionDate = new \DateTime();
        } elseif (false === $deleted && true === $this->deleted) {
            $this->deletionDate = null;
        }

        // If user delete a strain, wee need to delete all tubes
        if (true === $deleted) {
            foreach ($this->getTubes() as $tube) {
                $tube->setDeleted(true);
            }
        }

        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted.
     *
     * @return bool
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set comment.
     *
     * @param string $comment
     *
     * @return Strain
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set autoName.
     *
     * @param string $autoName
     *
     * @return Strain
     */
    public function setAutoName($autoName)
    {
        $this->autoName = $autoName;

        return $this;
    }

    /**
     * Get autoName.
     *
     * @return string
     */
    public function getAutoName()
    {
        return $this->autoName;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Strain
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
     * Set sequenced.
     *
     * @param bool $sequenced
     *
     * @return Strain
     */
    public function setSequenced($sequenced)
    {
        $this->sequenced = $sequenced;

        return $this;
    }

    /**
     * Get sequenced.
     *
     * @return bool
     */
    public function getSequenced()
    {
        return $this->sequenced;
    }

    /**
     * Get full name.
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->autoName.' - '.$this->name;
    }

    /**
     * Generate the auto name.
     */
    public function generateAutoName()
    {
        // The automatic name of the strain is the name of the first tube
        // when the strain is registred the first time
        $this->autoName = $this->getTubes()->first()->getName();
    }

    /**
     * Get the teams.
     *
     * @return array
     */
    public function getTeams()
    {
        $teams = [];

        foreach ($this->getTubes() as $tube) {
            $team = $tube->getBox()->getProject()->getTeam();

            if (!in_array($team, $teams)) {
                $teams[] = $team;
            }
        }

        return $teams;
    }

    /**
     * Get the projects.
     *
     * @return array
     */
    public function getProjects()
    {
        $projects = [];

        foreach ($this->getTubes() as $tube) {
            if (!in_array($project = $tube->getBox()->getProject(), $projects)) {
                $projects[] = $project;
            }
        }

        return $projects;
    }

    /**
     * Get allowed users.
     *
     * @return array
     */
    public function getAllowedUsers()
    {
        $allowedUsers = [];

        foreach ($this->getTubes() as $tube) {
            foreach ($tube->getBox()->getProject()->getMembers() as $member) {
                if (!in_array($member, $allowedUsers)) {
                    $allowedUsers[] = $member;
                }
            }
        }

        return $allowedUsers;
    }

    /**
     * Is allowed user ?
     *
     * @param User $user
     *
     * @return bool
     */
    public function isAllowedUser(User $user)
    {
        return in_array($user, $this->getAllowedUsers());
    }

    /**
     * Is author ?
     *
     * @param User $user
     *
     * @return bool
     */
    public function isAuthor(User $user)
    {
        return $user === $this->getAuthor();
    }

    /**
     * Set main Species.
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function lifeCycleMainSpecies()
    {
        // Define the main Species
        $species = $this->getSpecies();

        if (!$species->isMainSpecies()) {
            $this->setSpecies($species->getMainSpecies());
        }
    }

    /**
     * Set autoName.
     *
     * @ORM\PrePersist()
     */
    public function plifeCycleAutoName()
    {
        $project = $this->getTubes()->first()->getProject();
        $projectStrainNumber = $project->getLastStrainNumber() + 1;
        $projectPrefix = $project->getPrefix();

        $autoName = $projectPrefix.'_'.str_pad($projectStrainNumber, 4, '0', STR_PAD_LEFT);

        // Set autoName
        $this->setAutoName($autoName);
        $project->setLastStrainNumber($projectStrainNumber);

    }
}
