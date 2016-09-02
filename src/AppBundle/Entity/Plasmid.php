<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(name="systematicName", type="string", length=255, unique=true)
     */
    private $systematicName;

    /**
     * @var string
     *
     * @ORM\Column(name="usualName", type="string", length=255, unique=true)
     */
    private $usualName;

    /**
     * @var string
     *
     * @ORM\Column(name="sequence", type="text")
     */
    private $sequence;


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
     * Set systematicName
     *
     * @param string $systematicName
     *
     * @return Plasmid
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
     * @return Plasmid
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
     * Set sequence
     *
     * @param string $sequence
     *
     * @return Plasmid
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }

    /**
     * Get sequence
     *
     * @return string
     */
    public function getSequence()
    {
        return $this->sequence;
    }
}

