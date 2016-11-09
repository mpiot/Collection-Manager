<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Primer
 *
 * @ORM\Table(name="primer")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PrimerRepository")
 * @ORM\HasLifeCycleCallbacks()
 */
class Primer
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
     * @ORM\Column(name="autoName", type="string", length=255, unique=true)
     */
    private $autoName;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
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
     * @ORM\Column(name="orientation", type="string", length=255)
     */
    private $orientation;

    /**
     * @var string
     *
     * @ORM\Column(name="quality", type="string", length=255, nullable=true)
     */
    private $quality;

    /**
     * @var string
     *
     * @ORM\Column(name="sequence", type="string", length=255, unique=true)
     * @Assert\Regex("/[ATCG]+/i", message="An ADN sequence may only contain: A, T, C or G letters.")
     */
    private $sequence;

    /**
     * @var string
     *
     * @ORM\Column(name="fivePrimeExtension", type="string", length=255, nullable=true)
     * @Assert\Regex("/[ATCG]+/i", message="An ADN sequence may only contain: A, T, C or G letters.")
     */
    private $fivePrimeExtension;

    /**
     * @var string
     *
     * @ORM\Column(name="fivePrimeExtensionName", type="string", length=255, nullable=true)
     */
    private $fivePrimeExtensionName;

    /**
     * @var string
     *
     * @ORM\Column(name="threePrimeExtension", type="string", length=255, nullable=true)
     * @Assert\Regex("/[ATCG]+/i", message="An ADN sequence may only contain: A, T, C or G letters.")
     */
    private $threePrimeExtension;

    /**
     * @var string
     *
     * @ORM\Column(name="threePrimeExtensionName", type="string", length=255, nullable=true)
     */
    private $threePrimeExtensionName;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Team", inversedBy="primers")
     */
    private $team;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Plasmid", mappedBy="primers")
     */
    private $plasmids;

    /**
     * Primer constructor.
     */
    public function __construct()
    {
        $this->plasmids = new ArrayCollection();
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
     * Set autoName
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
     * Get autoName
     *
     * @return string
     */
    public function getAutoName()
    {
        return $this->autoName;
    }

    /**
     * Set name
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
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
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set orientation
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
     * Get orientation
     *
     * @return string
     */
    public function getOrientation()
    {
        return $this->orientation;
    }

    /**
     * Set quality
     *
     * @param string $quality
     *
     * @return Primer
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;

        return $this;
    }

    /**
     * Get quality
     *
     * @return string
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * Set sequence
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
     * Get sequence
     *
     * @return string
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * Set fivePrimeExtension
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
     * Get fivePrimeExtension
     *
     * @return string
     */
    public function getFivePrimeExtension()
    {
        return $this->fivePrimeExtension;
    }

    /**
     * Set fivePrimeExtensionName
     *
     * @param string $fivePrimeExtensionName
     *
     * @return Primer
     */
    public function setFivePrimeExtensionName($fivePrimeExtensionName)
    {
        $this->fivePrimeExtensionName = $fivePrimeExtensionName;

        return $this;
    }

    /**
     * Get fivePrimeExtensionName
     *
     * @return string
     */
    public function getFivePrimeExtensionName()
    {
        return $this->fivePrimeExtensionName;
    }

    /**
     * Set threePrimeExtension
     *
     * @param string $threePrimeExtension
     *
     * @return Primer
     */
    public function setThreePrimeExtension($threePrimeExtension)
    {
        $this->threePrimeExtension = strtoupper($threePrimeExtension);

        return $this;
    }

    /**
     * Get threePrimeExtension
     *
     * @return string
     */
    public function getThreePrimeExtension()
    {
        return $this->threePrimeExtension;
    }

    /**
     * Set threePrimeExtensionName
     *
     * @param string $threePrimeExtensionName
     *
     * @return Primer
     */
    public function setThreePrimeExtensionName($threePrimeExtensionName)
    {
        $this->threePrimeExtensionName = $threePrimeExtensionName;

        return $this;
    }

    /**
     * Get threePrimeExtensionName
     *
     * @return string
     */
    public function getThreePrimeExtensionName()
    {
        return $this->threePrimeExtensionName;
    }

    /**
     * Get SequenceWithExtensions.
     *
     * @return string
     */
    public function getSequenceWithExtensions()
    {
        return $this->fivePrimeExtension.$this->sequence.$this->threePrimeExtension;
    }

    /**
     * Get FormatedSequenceWithExtensions.
     *
     * @return string
     */
    public function getFormatedSequenceWithExtensions()
    {
        return '<b>'.$this->fivePrimeExtension.'</b>'.$this->sequence.'<b>'.$this->threePrimeExtension.'</b>';
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
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $primerNumber = $this->team->getLastPrimerNumber() + 1;

        if (1 !== $primerNumber) {
            // Determine how many 0 put before the number
            $nbDigit = 4;
            $numberOf0 = $nbDigit - ceil(log10($primerNumber));
            $autoName = 'primer'.str_repeat('0', $numberOf0).$primerNumber;
        } else {
            $autoName = 'primer0001';
        }

        $this->autoName = $autoName;
        $this->team->setLastPrimerNumber($primerNumber);
    }
}

