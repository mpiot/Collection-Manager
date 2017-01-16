<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BiologicalOriginCategory.
 *
 * @ORM\Table(name="biological_origin_category")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BiologicalOriginCategoryRepository")
 * @UniqueEntity("name", message="A category already exist with the name: {{ value }}.")
 */
class BiologicalOriginCategory
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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\WildStrain", mappedBy="biologicalOriginCategory")
     */
    private $wildStrains;

    public function __construct()
    {
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
     * @return BiologicalOriginCategory
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

    public function getWildStrains()
    {
        return $this->wildStrains;
    }
}
