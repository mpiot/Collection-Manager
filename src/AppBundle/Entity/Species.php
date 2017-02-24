<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Species.
 *
 * @ORM\Table(name="species")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SpeciesRepository")
 * @UniqueEntity({"genus", "name"}, message="This name is already used by another species.")
 */
class Species
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
     * @Gedmo\Slug(handlers={
     *      @Gedmo\SlugHandler(class="Gedmo\Sluggable\Handler\RelativeSlugHandler", options={
     *          @Gedmo\SlugHandlerOption(name="relationField", value="genus"),
     *          @Gedmo\SlugHandlerOption(name="relationSlugField", value="name"),
     *          @Gedmo\SlugHandlerOption(name="separator", value="-"),
     *          @Gedmo\SlugHandlerOption(name="urilize", value=true)
     *      })
     * }, fields={"name"})
     * @ORM\Column(name="slug", type="string", length=128, unique=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Genus", inversedBy="species")
     * @Assert\Valid
     */
    private $genus;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\Regex("#^sp.|(?:[a-z]+ var. )?[a-z]+$#m", message="The species must be in small letters, sp. or var. (eg: cerevisiae, sp., lactis var. lactis)")
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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Species", mappedBy="mainSpecies", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Assert\Valid
     */
    private $synonyms;

    /**
     * @var Strain|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Strain", mappedBy="species")
     */
    private $strains;


    public function __construct()
    {
        $this->synonyms = new ArrayCollection();
        $this->strains = new ArrayCollection();
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
     *
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
    public function getStrains()
    {
        return $this->strains;
    }

    /**
     * Get scientificName.
     *
     * @return string
     */
    public function getScientificName()
    {
        return $this->genus->getName().' '.$this->name;
    }

    public function isMainSpecies()
    {
        return (null !== $this->mainSpecies) ? false : true;
    }
}
