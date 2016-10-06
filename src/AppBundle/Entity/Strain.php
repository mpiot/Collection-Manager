<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Strain
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
     * @ORM\Column(name="comment", type="text")
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="systematicName", type="string", length=255, unique=true)
     */
    private $systematicName;

    /**
     * @var string
     *
     * @ORM\Column(name="usualName", type="string", length=255)
     */
    private $usualName;

    /**
     * @var bool
     *
     * @ORM\Column(name="sequenced", type="boolean")
     */
    private $sequenced;

    /**
     * @var Type
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Type", inversedBy="strains")
     */
    private $type;

    /**
     * @var Species
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Species", inversedBy="strains")
     * @ORM\JoinColumn(nullable=false)
     */
    private $species;
    

    public function __construct()
    {
        $this->creationDate = new \DateTime();
    }

    /**
     * Set creationDate
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
     * Get creationDate
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set deletionDate
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
     * Get deletionDate
     *
     * @return \DateTime
     */
    public function getDeletionDate()
    {
        return $this->deletionDate;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     *
     * @return Strain
     */
    public function setDeleted(bool $deleted)
    {
        if(true === $deleted && false === $this->deleted) {
            $this->deletionDate = new \DateTime();
        } elseif (false === $deleted && true === $this->deleted) {
            $this->deletionDate = null;
        }

        // If user delete a strain, wee need to delete all tubes
        if(true === $deleted) {
            foreach ($this->getTubes() as $tube) {
                $tube->setDeleted(true);
            }
        }

        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return bool
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set comment
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
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set systematicName
     *
     * @param string $systematicName
     *
     * @return Strain
     */
    public function setSystematicName($systematicName)
    {
        $this->systematicName = $systematicName;

        return $this;
    }

    /**
     * Get systematicName
     *
     * @return string
     */
    public function getSystematicName()
    {
        return $this->systematicName;
    }

    /**
     * Set usualName
     *
     * @param string $usualName
     *
     * @return Strain
     */
    public function setUsualName($usualName)
    {
        $this->usualName = $usualName;

        return $this;
    }

    /**
     * Get usualName
     *
     * @return string
     */
    public function getUsualName()
    {
        return $this->usualName;
    }

    /**
     * Set sequenced
     *
     * @param boolean $sequenced
     *
     * @return Strain
     */
    public function setSequenced($sequenced)
    {
        $this->sequenced = $sequenced;

        return $this;
    }

    /**
     * Get sequenced
     *
     * @return bool
     */
    public function getSequenced()
    {
        return $this->sequenced;
    }

    /**
     * Set type
     *
     * @param Type $type
     *
     * @return Strain
     */
    public function setType(Type $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set species
     *
     * @param Species $species
     * @return $this
     */
    public function setSpecies(Species $species)
    {
        $this->species = $species;

        return $this;
    }

    /**
     * Get species
     *
     * @return Species
     */
    public function getSpecies()
    {
        return $this->species;
    }

    /**
     * Get full name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->systematicName.' - '.$this->usualName;
    }

    /**
     * Generate the auto name
     */
    public function generateAutoName()
    {
        // The automatic name of the strain is the name of the first tube
        // when the strain is registred the first time
        $this->systematicName = $this->getTubes()->first()->getName();
    }
}
