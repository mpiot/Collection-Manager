<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Group.
 *
 * @ORM\Table(name="`group`")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GroupRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("name", message="A group already exists with this name.")
 */
class Group
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
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(name="slug", length=128, unique=true)
     */
    private $slug;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", inversedBy="administeredGroups")
     * @ORM\JoinTable(name="group_administrators")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\Count(min="1", minMessage = "You must specify at least one administrator.")
     */
    private $administrators;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", inversedBy="groups")
     * @ORM\JoinTable(name="group_members")
     * @ORM\JoinColumn(nullable=false)
     */
    private $members;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Box", mappedBy="group", cascade={"remove"})
     */
    private $boxes;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Strain", mappedBy="group", cascade={"remove"})
     */
    private $strains;

    /**
     * @ORM\Column(name="last_strain_number", type="integer", nullable=false)
     */
    private $lastStrainNumber;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Plasmid", mappedBy="group", cascade={"remove"})
     */
    private $plasmids;

    /**
     * @ORM\Column(name="last_plasmid_number", type="integer", nullable=false)
     */
    private $lastPlasmidNumber;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Primer", mappedBy="group", cascade={"remove"})
     */
    private $primers;

    /**
     * @ORM\Column(name="last_primer_number", type="integer", nullable=false)
     */
    private $lastPrimerNumber;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Product", mappedBy="group", cascade={"remove"})
     */
    private $products;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Equipment", mappedBy="group", cascade={"remove"})
     */
    private $equipments;

    /**
     * Group constructor.
     */
    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->administrators = new ArrayCollection();
        $this->boxes = new ArrayCollection();
        $this->types = new ArrayCollection();
        $this->biologicalOriginCategories = new ArrayCollection();
        $this->strains = new ArrayCollection();
        $this->lastStrainNumber = 0;
        $this->plasmids = new ArrayCollection();
        $this->lastPlasmidNumber = 0;
        $this->primers = new ArrayCollection();
        $this->lastPrimerNumber = 0;
        $this->products = new ArrayCollection();
        $this->equipments = new ArrayCollection();
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
     * @return Group
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
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Add administrator.
     *
     * @param User $user
     *
     * @return Group
     */
    public function addAdministrator(User $user)
    {
        $user->addAdministeredGroup($this);
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
     * @return Group
     */
    public function addMember(User $user)
    {
        $user->addGroup($this);
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
     * Add box.
     *
     * @param Box $box
     *
     * @return Group
     */
    public function addBox(Box $box)
    {
        $this->boxes->add($box);

        return $this;
    }

    /**
     * Remove box.
     *
     * @param Box $box
     *
     * @return Group
     */
    public function removeBox(Box $box)
    {
        $this->boxes->removeElement($box);

        return $this;
    }

    /**
     * Get boxes.
     *
     * @return ArrayCollection
     */
    public function getBoxes()
    {
        return $this->boxes;
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
     *
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
     *
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

    /**
     * Get products.
     *
     * @return ArrayCollection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Get equipments.
     *
     * @return ArrayCollection
     */
    public function getEquipments()
    {
        return $this->equipments;
    }
}
