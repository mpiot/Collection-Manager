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
 * @UniqueEntity({"team", "name"}, message="A category already exist with the name: {{ value }}.")
 */
class BiologicalOriginCategory
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Team", inversedBy="biologicalOriginCategories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $team;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Strain", mappedBy="biologicalOriginCategory")
     */
    private $strains;

    public function __construct()
    {
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
     * Set name.
     *
     * @param string $name
     *
     * @return BiologicalOriginCategory
     */
    public function setName($name)
    {
        $this->name = ucfirst($name);

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

    public function getStrains()
    {
        return $this->strains;
    }

    /**
     * Set team.
     *
     * @param Team $team
     *
     * @return BiologicalOriginCategory
     */
    public function setTeam(Team $team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team.
     *
     * @return Team
     */
    public function getTeam()
    {
        return $this->team;
    }
}
