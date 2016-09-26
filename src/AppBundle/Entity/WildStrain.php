<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Wild
 *
 * @ORM\Table(name="wild_strain")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\WildStrainRepository")
 */
class WildStrain extends Strain
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
     * @ORM\Column(name="address", type="string", length=255)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255)
     */
    private $country;

    /**
     * @var BiologicalOriginCategory
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\BiologicalOriginCategory")
     */
    private $biologicalOriginCategory;

    /**
     * @var string
     *
     * @ORM\Column(name="biologicalOrigin", type="string", length=255)
     */
    private $biologicalOrigin;

    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=255)
     */
    private $source;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="float")
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="float")
     */
    private $longitude;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Tube", mappedBy="wildStrain", cascade={"persist", "remove"})
     */
    private $tubes;


    public function __construct()
    {
        parent::__construct();
        $this->tubes = new ArrayCollection();
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
     * Set address
     *
     * @param string $address
     *
     * @return WildStrain
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set Biological origin category
     *
     * @param $category
     * @return $this
     */
    public function setBiologicalOriginCategory($category)
    {
        $this->biologicalOriginCategory = $category;

        return $this;
    }

    /**
     * Get biological origin category
     *
     * @return BiologicalOriginCategory
     */
    public function getBiologicalOriginCategory()
    {
        return $this->biologicalOriginCategory;
    }

    /**
     * Set biologicalOrigin
     *
     * @param string $biologicalOrigin
     *
     * @return WildStrain
     */
    public function setBiologicalOrigin($biologicalOrigin)
    {
        $this->biologicalOrigin = $biologicalOrigin;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return WildStrain
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get biologicalOrigin
     *
     * @return string
     */
    public function getBiologicalOrigin()
    {
        return $this->biologicalOrigin;
    }

    /**
     * Set source
     *
     * @param string $source
     *
     * @return WildStrain
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set latitude.
     * 
     * @param $latitude
     * @return $this
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude.
     * 
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude.
     *
     * @param $latitude
     * @return $this
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude.
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    public function addTube(Tube $tube)
    {
        if (!$this->tubes->contains($tube)) {
            $tube->setWildStrain($this);
            $this->tubes->add($tube);
        }
    }

    public function removeTube(Tube $tube)
    {
        if ($this->tubes->contains($tube)) {
            $this->tubes->removeElement($tube);
        }
    }

    public function getTubes()
    {
        return $this->tubes;
    }
}
