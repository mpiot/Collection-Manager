<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * BiologicalOriginCategory.
 *
 * @ORM\Table(name="biological_origin_category")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BiologicalOriginCategoryRepository")
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\WildStrain", mappedBy="biologicalOriginCategory")
     */
    private $wildStrains;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Team", inversedBy="biologicalOriginCategories")
     */
    private $team;

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

    public function setTeam(Team $team)
    {
        $this->team = $team;

        return $this;
    }

    public function getTeam()
    {
        return $this->team;
    }
}
