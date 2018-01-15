<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Primer.
 *
 * @ORM\Table(name="primer")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PrimerRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Primer
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
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="orientation", type="string", length=255, nullable=true)
     */
    private $orientation;

    /**
     * @var string
     *
     * @ORM\Column(name="sequence", type="string", length=255)
     * @Assert\Regex("/^[ACGTNUKSYMWRBDHV-]+$/i", message="Please, see as the allowed letters in the table on the bottom of the page.")
     */
    private $sequence;

    /**
     * @var string
     *
     * @ORM\Column(name="fivePrimeExtension", type="string", length=255, nullable=true)
     * @Assert\Regex("/^[ACGTNUKSYMWRBDHV-]+$/i", message="Please, see as the allowed letters in the table on the bottom of the page.")
     */
    private $fivePrimeExtension;

    /**
     * @var string
     *
     * @ORM\Column(name="labelMarker", type="string", length=255, nullable=true)
     */
    private $labelMarker;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Group", inversedBy="primers")
     */
    private $group;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Plasmid", mappedBy="primers")
     */
    private $plasmids;

    /**
     * @var string
     *
     * @ORM\Column(name="hybridationTemp", type="float", nullable=true)
     */
    private $hybridationTemp;

    /**
     * @var ArrayCollection|Tube
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Tube", mappedBy="primer", cascade={"persist", "remove"}, orphanRemoval=true)
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
     * Primer constructor.
     */
    public function __construct()
    {
        $this->plasmids = new ArrayCollection();
        $this->tubes = new ArrayCollection();
    }

    /**
     * To string.
     *
     * @return string
     */
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
     * @return Primer
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
     * @return Primer
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
     * Set description.
     *
     * @param string $description
     *
     * @return Primer
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
     * Set orientation.
     *
     * @param string $orientation
     *
     * @return Primer
     */
    public function setOrientation($orientation)
    {
        $this->orientation = $orientation;

        return $this;
    }

    /**
     * Get orientation.
     *
     * @return string
     */
    public function getOrientation()
    {
        return $this->orientation;
    }

    /**
     * Set sequence.
     *
     * @param string $sequence
     *
     * @return Primer
     */
    public function setSequence($sequence)
    {
        $this->sequence = strtoupper($sequence);

        return $this;
    }

    /**
     * Get sequence.
     *
     * @return string
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * Set fivePrimeExtension.
     *
     * @param string $fivePrimeExtension
     *
     * @return Primer
     */
    public function setFivePrimeExtension($fivePrimeExtension)
    {
        $this->fivePrimeExtension = strtoupper($fivePrimeExtension);

        return $this;
    }

    /**
     * Get fivePrimeExtension.
     *
     * @return string
     */
    public function getFivePrimeExtension()
    {
        return $this->fivePrimeExtension;
    }

    /**
     * Set LabelMarker.
     *
     * @param string $labelMarker
     *
     * @return Primer
     */
    public function setLabelMarker($labelMarker)
    {
        $this->labelMarker = $labelMarker;

        return $this;
    }

    /**
     * Get LabelMarker.
     *
     * @return string
     */
    public function getLabelMarker()
    {
        return $this->labelMarker;
    }

    /**
     * Set Hybridation Temperature.
     *
     * @param string $hybridationTemp
     *
     * @return Primer
     */
    public function setHybridationTemp($hybridationTemp)
    {
        $this->hybridationTemp = $hybridationTemp;

        return $this;
    }

    /**
     * Get Hybridation Temperature.
     *
     * @return string
     */
    public function getHybridationTemp()
    {
        return $this->hybridationTemp;
    }

    /**
     * Set group.
     *
     * @param Group $group
     *
     * @return Primer
     */
    public function setGroup(Group $group)
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
     * Get plasmids.
     *
     * @return ArrayCollection
     */
    public function getPlasmids()
    {
        return $this->plasmids;
    }

    /**
     * Add tube.
     *
     * @param Tube $tube
     *
     * @return Primer
     */
    public function addTube(Tube $tube)
    {
        if (!$this->tubes->contains($tube)) {
            $tube->setPrimer($this);
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
        $primerNumber = $this->getGroup()->getLastPrimerNumber() + 1;
        $autoName = str_pad($primerNumber, 4, '0', STR_PAD_LEFT);

        // Set autoName
        $this->setAutoName($autoName);
        $this->getGroup()->setLastPrimerNumber($primerNumber);
    }
}
