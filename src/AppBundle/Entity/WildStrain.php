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
     * @ORM\Column(name="geographicOrigin", type="string", length=255)
     */
    private $geographicOrigin;

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
     * Set geographicOrigin
     *
     * @param string $geographicOrigin
     *
     * @return WildStrain
     */
    public function setGeographicOrigin($geographicOrigin)
    {
        $this->geographicOrigin = $geographicOrigin;

        return $this;
    }

    /**
     * Get geographicOrigin
     *
     * @return string
     */
    public function getGeographicOrigin()
    {
        return $this->geographicOrigin;
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
