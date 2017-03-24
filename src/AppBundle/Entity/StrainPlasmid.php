<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StrainPlasmid.
 *
 * @ORM\Table(name="strain_plasmid")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StrainPlasmidRepository")
 */
class StrainPlasmid
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
     * @var Strain
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Strain", inversedBy="strainPlasmids")
     * @ORM\JoinColumn(nullable=false)
     */
    private $strain;

    /**
     * @var Plasmid
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Plasmid", inversedBy="strainPlasmids")
     * @ORM\JoinColumn(nullable=false)
     */
    private $plasmid;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=255)
     */
    private $state;

    public function __toString()
    {
        return $this->getPlasmid().' ('.$this->state.')';
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
     * Set strain.
     *
     * @param Strain $strain
     *
     * @return StrainPlasmid
     */
    public function setStrain(Strain $strain)
    {
        $this->strain = $strain;

        return $this;
    }

    /**
     * Get strain.
     *
     * @return Strain
     */
    public function getStrain()
    {
        return $this->strain;
    }

    /**
     * Set plasmid.
     *
     * @param Plasmid $plasmid
     *
     * @return StrainPlasmid
     */
    public function setPlasmid(Plasmid $plasmid)
    {
        $this->plasmid = $plasmid;

        return $this;
    }

    /**
     * Get plasmid.
     *
     * @return Plasmid
     */
    public function getPlasmid()
    {
        return $this->plasmid;
    }

    /**
     * Set state.
     *
     * @param string $state
     *
     * @return StrainPlasmid
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state.
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }
}
