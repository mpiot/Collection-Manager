<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * GMO
 * 
 * @ORM\Table(name="gmo_strain")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GmoStrainRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class GmoStrain extends Strain
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
     * @ORM\Column(name="genotype", type="text")
     */
    private $genotype;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Tube", mappedBy="gmoStrain", cascade={"persist", "remove"})
     */
    private $tubes;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\StrainPlasmid", mappedBy="gmoStrain", cascade={"persist", "remove"})
     */
    private $strainPlasmids;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\GmoStrain", inversedBy="children")
     * @ORM\JoinColumn(nullable=true)
     */
    private $parents;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\GmoStrain", mappedBy="parents")
     */
    private $children;

    public function __construct()
    {
        parent::__construct();
        $this->tubes = new ArrayCollection();
        $this->strainPlasmids = new ArrayCollection();
        $this->parents = new ArrayCollection();
        $this->children = new ArrayCollection();
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
     * Set genotype
     *
     * @param string $genotype
     *
     * @return GmoStrain
     */
    public function setGenotype($genotype)
    {
        $this->genotype = $genotype;

        return $this;
    }

    /**
     * Get genotype
     *
     * @return string
     */
    public function getGenotype()
    {
        return $this->genotype;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return GmoStrain
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

    public function addTube(Tube $tube)
    {
        $tube->setGmoStrain($this);
        $this->tubes->add($tube);
    }

    public function removeTube(Tube $tube)
    {
        $this->tubes->removeElement($tube);
    }

    public function getTubes()
    {
        return $this->tubes;
    }

    public function addStrainPlasmid(StrainPlasmid $strainPlasmid)
    {
        $strainPlasmid->setGmoStrain($this);
        $this->strainPlasmids->add($strainPlasmid);

        return $this;
    }

    public function removeStrainPlasmid(StrainPlasmid $strainPlasmid)
    {
        $this->strainPlasmids->removeElement($strainPlasmid);

        return $this;
    }

    public function getStrainPlasmids()
    {
        return $this->strainPlasmids;
    }

    public function addParent(GmoStrain $strain)
    {
        $this->parents->add($strain);
    }

    public function removeParent(GmoStrain $strain)
    {
        $this->parents->removeElement($strain);
    }

    public function getParents()
    {
        return $this->parents;
    }

    public function getChildren()
    {
        return $this->children;
    }
}
