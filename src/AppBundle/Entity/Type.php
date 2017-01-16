<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Type.
 *
 * @ORM\Table(name="type")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TypeRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("name", message="A type already exist with the name: {{ value }}.")
 */
class Type
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
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Regex("/^[A-Z]/", message="A type must start by a capital letter.")
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\GmoStrain", mappedBy="type")
     */
    private $gmoStrains;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\WildStrain", mappedBy="type")
     */
    private $wildStrains;

    public function __construct()
    {
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
     * Set name.
     *
     * @param string $name
     *
     * @return Type
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function getGmoStrains()
    {
        return $this->gmoStrains;
    }

    public function getWildStrains()
    {
        return $this->wildStrains;
    }
}
