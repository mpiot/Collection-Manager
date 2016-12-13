<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * GMO.
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="gmoStrains")
     */
    private $author;

    /**
     * @var Species
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Species", inversedBy="gmoStrains")
     * @ORM\JoinColumn(nullable=false)
     */
    private $species;

    /**
     * @var Type
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Type", inversedBy="gmoStrains")
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="genotype", type="text", nullable=true)
     */
    private $genotype;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
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
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set species.
     *
     * @param Species $species
     *
     * @return $this
     */
    public function setSpecies(Species $species)
    {
        $this->species = $species;

        return $this;
    }

    /**
     * Get species.
     *
     * @return Species
     */
    public function getSpecies()
    {
        return $this->species;
    }

    /**
     * Set type.
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
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set genotype.
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
     * Get genotype.
     *
     * @return string
     */
    public function getGenotype()
    {
        return $this->genotype;
    }

    /**
     * Set description.
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
     * Get description.
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
