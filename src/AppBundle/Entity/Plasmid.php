<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Plasmid.
 *
 * @ORM\Table(name="plasmid")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PlasmidRepository")
 * @ORM\HasLifeCycleCallbacks()
 */
class Plasmid
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
     * @ORM\Column(name="autoName", type="string", length=255)
     */
    private $autoName;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Team", inversedBy="plasmids")
     */
    private $team;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\GenBankFile", mappedBy="plasmid", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
     */
    private $genBankFile;

    private $addGenBankFile = false;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\StrainPlasmid", mappedBy="plasmid")
     */
    private $strainPlasmids;

    public function __construct()
    {
        $this->strainPlasmids = new ArrayCollection();
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
     * @return Plasmid
     */
    public function setAutoName($autoName)
    {
        $this->autoName = $autoName;

        return $this;
    }

    /**
     * Get systematicName.
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
     * Set team.
     *
     * @param Team $team
     *
     * @return Plasmid
     */
    public function setTeam($team)
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
     * Set genBank file.
     *
     * @param string $genBankFile
     *
     * @return Plasmid
     */
    public function setGenBankFile($genBankFile)
    {
        $this->genBankFile = $genBankFile;

        return $this;
    }

    /**
     * Get genBank file.
     *
     * @return string
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
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $plasmidNumber = $this->team->getLastPlasmidNumber() + 1;

        if (1 !== $plasmidNumber) {
            // Determine how many 0 put before the number
            $nbDigit = 4;
            $numberOf0 = $nbDigit - ceil(log10($plasmidNumber));
            $autoName = 'p'.str_repeat('0', $numberOf0).$plasmidNumber;
        } else {
            $autoName = 'p0001';
        }

        $this->autoName = $autoName;
        $this->team->setLastPlasmidNumber($plasmidNumber);
    }
}
