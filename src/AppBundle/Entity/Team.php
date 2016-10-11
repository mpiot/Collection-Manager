<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Team
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
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", inversedBy="administeredTeams", orphanRemoval=true)
     * @ORM\JoinTable(name="team_administrators")
     * @ORM\JoinColumn(nullable=false)
     */
    private $administrators;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", inversedBy="moderatedTeams", orphanRemoval=true)
     * @ORM\JoinTable(name="team_moderators")
     * @ORM\JoinColumn(nullable=true)
     */
    private $moderators;

    /**
     * @var string
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", inversedBy="teams", orphanRemoval=true)
     * @ORM\JoinTable(name="team_members")
     * @ORM\JoinColumn(nullable=true)
     */
    private $members;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->administrators = new ArrayCollection();
        $this->moderators = new ArrayCollection();
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
     * @return Team
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
     * Add administrator
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
     * Remove administrator
     *
     * @param User $user
     */
    public function removeAdministrator(User $user)
    {
        $this->administrators->removeElement($user);
    }

    /**
     * Get administrators
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdministrators()
    {
        return $this->administrators;
    }

    /**
     * Add moderator
     *
     * @param User $user
     *
     * @return Team
     */
    public function addModerator(User $user)
    {
        $user->addModeratedTeam($this);
        $this->moderators->add($user);

        // When you add a user as a moderator, add it as a member too
        $this->addMember($user);

        return $this;
    }

    /**
     * Remove moderator
     *
     * @param User $user
     */
    public function removeModerator(User $user)
    {
        $this->moderators->removeElement($user);
    }

    /**
     * Get moderators
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getModerators()
    {
        return $this->moderators;
    }

    /**
     * Add member
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
     * Remove member
     *
     * @param User $user
     */
    public function removeMember(User $user)
    {
        $this->members->removeElement($user);
    }

    /**
     * Get members
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Before persist or update.
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function prePersist()
    {
        // Check if administrator is a member, if not add it
        foreach ($this->administrators as $administrator) {
            if (!$this->members->contains($administrator)) {
                $this->addMember($administrator);
            }
        }

        // Check the same for moderator
        foreach($this->moderators as $moderator) {
            if (!$this->members->contains($moderator)) {
                $this->addMember($moderator);
            }
        }
    }
}
