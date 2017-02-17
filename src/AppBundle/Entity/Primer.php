<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Primer.
 *
 * @ORM\Table(name="primer")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PrimerRepository")
 * @ORM\HasLifeCycleCallbacks()
 * @UniqueEntity({"name", "team"}, message="This name is already used by another primer.")
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
     * @Assert\Regex("/[ACGTNUKSYMWRBDHV-]+/i", message="Please, see as the allowed letters in the table on the bottom of the page.")
     */
    private $sequence;

    /**
     * @var string
     *
     * @ORM\Column(name="fivePrimeExtension", type="string", length=255, nullable=true)
     * @Assert\Regex("/[ACGTNUKSYMWRBDHV-]+/i", message="Please, see as the allowed letters in the table on the bottom of the page.")
     */
    private $fivePrimeExtension;

    /**
     * @var string
     *
     * @ORM\Column(name="labelMarker", type="string", length=255, nullable=true)
     */
    private $labelMarker;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Team", inversedBy="primers")
     */
    private $team;

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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="last_editor", nullable=true)
     */
    private $lastEditor;

    /**
     * @ORM\Column(name="last_edit", type="datetime", nullable=true)
     */
    private $lastEdit;

    /**
     * Primer constructor.
     */
    public function __construct()
    {
        $this->plasmids = new ArrayCollection();
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
     * Get SequenceWithExtensions.
     *
     * @return string
     */
    public function getSequenceWithExtensions()
    {
        return $this->fivePrimeExtension.$this->sequence;
    }

    /**
     * Get FormatedSequenceWithExtensions.
     *
     * @return string
     */
    public function getFormatedSequenceWithExtensions()
    {
        return '<b>'.$this->fivePrimeExtension.'</b>'.$this->sequence;
    }

    /**
     * Set team.
     *
     * @param Team $team
     *
     * @return Primer
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
     * Get plasmids.
     *
     * @return ArrayCollection
     */
    public function getPlasmids()
    {
        return $this->plasmids;
    }

    /**
     * Set author.
     *
     * @param User $user
     *
     * @return $this
     */
    public function setAuthor(User $user)
    {
        $this->author = $user;

        return $this;
    }

    /**
     * Get author.
     *
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set lastEditor.
     *
     * @param User $user
     *
     * @return $this
     */
    public function setLastEditor(User $user)
    {
        $this->lastEditor = $user;

        return $this;
    }

    /**
     * Get lastEditor.
     *
     * @return User
     */
    public function getLastEditor()
    {
        return $this->lastEditor;
    }

    /**
     * Set lastEdit.
     *
     * @param \DateTime $lastEdit
     *
     * @return $this
     */
    public function setLastEdit(\DateTime $lastEdit)
    {
        $this->lastEdit = $lastEdit;

        return $this;
    }

    /**
     * Get lastEdit.
     *
     * @return mixed
     */
    public function getLastEdit()
    {
        return $this->lastEdit;
    }
}
