<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Species.
 *
 * @ORM\Table(name="species")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SpeciesRepository")
 */
class Species
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Genus", inversedBy="species", cascade={"remove"})
     */
    private $genus;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\Regex("#^[a-z]*$#", message="The species is in small letters. (eg: cerevisiae)")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="taxId", type="integer", nullable=true)
     * @Assert\Type("integer")
     */
    private $taxId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Species", inversedBy="synonyms")
     */
    private $mainSpecies;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Species", mappedBy="mainSpecies", cascade={"persist", "remove"})
     */
    private $synonyms;

    /**
     * @var Strain|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\GmoStrain", mappedBy="species")
     */
    private $gmoStrains;

    /**
     * @var Strain|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\WildStrain", mappedBy="species")
     */
    private $wildStrains;

    public function __construct()
    {
        $this->synonyms = new ArrayCollection();
        $this->gmoStrains = new ArrayCollection();
        $this->wildStrains = new ArrayCollection();
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
     * Set genus.
     *
     * @param string $genus
     *
     * @return Species
     */
    public function setGenus(Genus $genus)
    {
        $this->genus = $genus;

        return $this;
    }

    /**
     * Get genus.
     *
     * @return string
     */
    public function getGenus()
    {
        return $this->genus;
    }

    /**
     * Set species.
     *
     * @param string $species
     *
     * @return Species
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get species.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set TaxId.
     *
     * @param $taxId
     *
     * @return $this
     */
    public function setTaxId($taxId)
    {
        $this->taxId = $taxId;

        return $this;
    }

    /**
     * Get TaxId.
     *
     * @return string
     */
    public function getTaxId()
    {
        return $this->taxId;
    }

    /**
     * Set main.
     * @param Species $species
     *
     * @return $this
     */
    public function setMainSpecies(Species $species)
    {
        $this->mainSpecies = $species;

        return $this;
    }

    /**
     * Get main.
     *
     * @return Species
     */
    public function getMainSpecies()
    {
        return $this->mainSpecies;
    }

    /**
     * Add synonym.
     *
     * @param Species $species
     *
     * @return $this
     */
    public function addSynonym(Species $species)
    {
        $species->setMainSpecies($this);
        $this->synonyms->add($species);

        return $this;
    }

    /**
     * Remove synoym.
     *
     * @param Species $species
     *
     * @return $this
     */
    public function removeSynonym(Species $species)
    {
        $this->synonyms->removeElement($species);

        return $this;
    }

    /**
     * Get synonyms.
     *
     * @return ArrayCollection
     */
    public function getSynonyms()
    {
        return $this->synonyms;
    }

    /**
     * Get gmo strains.
     */
    public function getGmoStrains()
    {
        return $this->gmoStrains;
    }

    /**
     * Get wild strains.
     */
    public function getWildStrains()
    {
        return $this->wildStrains;
    }

    /**
     * Get scientificName.
     *
     * @return string
     */
    public function getScientificName()
    {
        return $this->genus->getGenus().' '.$this->name;
    }

    public function isMainSpecies()
    {
        return (null !== $this->mainSpecies) ? false : true;
    }
}
