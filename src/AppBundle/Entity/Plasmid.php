<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Plasmid.
 *
 * @ORM\Table(name="plasmid")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PlasmidRepository")
 * @ORM\HasLifecycleCallbacks()
 * @Vich\Uploadable
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
     * @Gedmo\Slug(fields={"name"}, unique=false)
     * @ORM\Column(name="slug", type="string", length=128)
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
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Group", inversedBy="plasmids")
     */
    private $group;

    /**
     * @Vich\UploadableField(mapping="plasmid_file", fileNameProperty="genBankName", size="genBankSize")
     */
    private $genBankFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $genBankName;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    private $genBankSize;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $genBankUpdatedAt;

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
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     *
     * @return Plasmid
     */
    public function setGenBankFile(File $genBankFile = null)
    {
        $this->genBankFile = $genBankFile;

        if ($genBankFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->genBankUpdatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    /**
     * @return File|null
     */
    public function getGenBankFile()
    {
        return $this->genBankFile;
    }

    /**
     * @param string $genBankName
     *
     * @return Plasmid
     */
    public function setGenBankName($genBankName)
    {
        $this->genBankName = $genBankName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getGenBankName()
    {
        return $this->genBankName;
    }

    /**
     * @param int $genBankSize
     *
     * @return Plasmid
     */
    public function setGenBankSize($genBankSize)
    {
        $this->genBankSize = $genBankSize;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getGenBankSize()
    {
        return $this->genBankSize;
    }

    /**
     * @return \Datetime
     */
    public function getGenBankUpdatedAt()
    {
        return $this->genBankUpdatedAt;
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
        if (!$this->tubes->contains($tube)) {
            $tube->setPlasmid($this);
            $this->tubes->add($tube);
        }

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
        if ($this->tubes->contains($tube)) {
            $this->tubes->removeElement($tube);
        }

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
