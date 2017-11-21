<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Plasmid.
 *
 * @ORM\Table(name="plasmid")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PlasmidRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity({"name", "group"}, message="This name is already used by another plasmid.")
 */
class Plasmid
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
     * @ORM\Column(name="autoName", type="string", length=255)
     */
    private $autoName;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Group", inversedBy="plasmids")
     */
    private $group;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\File", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
     */
    private $genBankFile;

    private $addGenBankFile = false;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\StrainPlasmid", mappedBy="plasmid")
     */
    private $strainPlasmids;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Primer", inversedBy="plasmids")
     */
    private $primers;

    /**
     * @var ArrayCollection|Tube
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Tube", mappedBy="plasmid", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Assert\Valid
     */
    private $tubes;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @var User
     *
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $createdBy;

    /**
     * @var User
     *
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id")
     */
    private $updatedBy;

    /**
     * Plasmid constructor.
     */
    public function __construct()
    {
        $this->strainPlasmids = new ArrayCollection();
        $this->primers = new ArrayCollection();
        $this->tubes = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->autoName.' - '.$this->name;
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
     * Set autoName.
     *
     * @param string $autoName
     *
     * @return Plasmid
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
     * @return Plasmid
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
     * Set group.
     *
     * @param Group $group
     *
     * @return Plasmid
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group.
     *
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set genBank file.
     *
     * @param File $genBankFile
     *
     * @return Plasmid
     */
    public function setGenBankFile(File $genBankFile = null)
    {
        $this->genBankFile = $genBankFile;

        return $this;
    }

    /**
     * Get genBank file.
     *
     * @return File
     */
    public function getGenBankFile()
    {
        return $this->genBankFile;
    }

    public function hasGenBankFile()
    {
        return (null !== $this->genBankFile) ? true : false;
    }

    /**
     * @param $addManual
     *
     * @return $this
     */
    public function setAddGenBankFile($addGenBankFile)
    {
        $this->addGenBankFile = $addGenBankFile;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddGenBankFile()
    {
        return $this->addGenBankFile;
    }

    /**
     * @return ArrayCollection
     */
    public function getStrainPlasmids()
    {
        return $this->strainPlasmids;
    }

    /**
     * @return ArrayCollection
     */
    public function getStrains()
    {
        $strains = new ArrayCollection();

        foreach ($this->strainPlasmids as $strainPlasmid) {
            $strains->add($strainPlasmid->getStrain());
        }

        return $strains;
    }

    /**
     * Add primer.
     *
     * @param Primer $primer
     */
    public function addPrimer(Primer $primer)
    {
        if (!$this->primers->contains($primer)) {
            $this->primers->add($primer);
        }
    }

    /**
     * Remove primer.
     *
     * @param Primer $primer
     */
    public function removePrimer(Primer $primer)
    {
        if ($this->primers->contains($primer)) {
            $this->primers->removeElement($primer);
        }
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
     * Add tube.
     *
     * @param Tube $tube
     *
     * @return Plasmid
     */
    public function addTube(Tube $tube)
    {
        $tube->setPlasmid($this);
        $this->tubes->add($tube);

        return $this;
    }

    /**
     * Remove tube.
     *
     * @param Tube $tube
     *
     * @return $this
     */
    public function removeTube(Tube $tube)
    {
        $this->tubes->removeElement($tube);

        return $this;
    }

    /**
     * Get tubes.
     *
     * @return Tube|ArrayCollection
     */
    public function getTubes()
    {
        return $this->tubes;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Get created by.
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
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
        return $user === $this->createdBy;
    }

    /**
     * Get updated by.
     *
     * @return User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Before persist.
     *
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $plasmidNumber = $this->getGroup()->getLastPlasmidNumber() + 1;
        $autoName = str_pad($plasmidNumber, 4, '0', STR_PAD_LEFT);

        // Set autoName
        $this->setAutoName($autoName);
        $this->getGroup()->setLastPlasmidNumber($plasmidNumber);
    }
}
