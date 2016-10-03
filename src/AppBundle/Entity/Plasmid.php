<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Plasmid
 *
 * @ORM\Table(name="plasmid")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PlasmidRepository")
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
     * @return Plasmid
     */
    public function setAutoName($autoName)
    {
        $this->autoName = $autoName;

        return $this;
    }

    /**
     * Get systematicName
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
     * @return Plasmid
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

    /**
     * @param $addManual
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

    public function getStrainPlasmids()
    {
        return $this->strainPlasmids;
    }
}
