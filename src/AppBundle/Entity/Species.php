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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Genus", inversedBy="species")
     */
    private $genus;

    /**
     * @var string
     *
     * @ORM\Column(name="species", type="string", length=255)
     * @Assert\Regex("#^[a-z]*$#", message="The species is in small letters. (eg: cerevisiae)")
     */
    private $species;

    /**
     * @var array
     *
     * @ORM\Column(name="synonyms", type="array")
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
        $this->synonyms = array();
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
    public function setSpecies($species)
    {
        $this->species = $species;

        return $this;
    }

    /**
     * Get species.
     *
     * @return string
     */
    public function getSpecies()
    {
        return $this->species;
    }

    /**
     * @param $synonym
     *
     * @return $this
     */
    public function addSynonym($synonym)
    {
        if (!empty($synonym) && !in_array($synonym, $this->synonyms, true)) {
            $this->synonyms[] = $synonym;
        }

        return $this;
    }

    /**
     * @param $synonym
     *
     * @return $this
     */
    public function removeSynonym($synonym)
    {
        if (false !== $key = array_search($synonym, $this->synonyms, true)) {
            unset($this->synonyms[$key]);
            $this->synonyms = array_values($this->synonyms);
        }

        return $this;
    }

    /**
     * Set synonyms.
     *
     * @param array $synonyms
     *
     * @return Species
     */
    public function setSynonyms($synonyms)
    {
        foreach ($synonyms as $synonym) {
            $this->addSynonym($synonym);
        }

        return $this;
    }

    /**
     * Get synonyms.
     *
     * @return array
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
     * @return string
     */
    public function getScientificName()
    {
        return $this->genus->getGenus().' '.$this->species;
    }
}
