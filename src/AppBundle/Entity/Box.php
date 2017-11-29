<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Box.
 *
 * @ORM\Table(name="box")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BoxRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity({"name", "group"}, message="A box already exist with the name: {{ value }}.")
 */
class Box
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
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(name="slug", type="string", length=128, unique=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="freezer", type="string", length=255)
     */
    private $freezer;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=255)
     */
    private $location;

    /**
     * @var int
     *
     * @ORM\Column(name="colNumber", type="integer")
     * @Assert\Range(
     *   min = 1,
     *   max = 26,
     *   minMessage = "The box must have at least {{ limit }} columns.",
     *   maxMessage = "The box can't have more than {{ limit }} columns."
     * )
     */
    private $colNumber;

    /**
     * @var int
     *
     * @ORM\Column(name="rowNumber", type="integer")
     * @Assert\Range(
     *   min = 1,
     *   max = 26,
     *   minMessage = "The box must have at least {{ limit }} rows.",
     *   maxMessage = "The box can't have more than {{ limit }} rows."
     * )
     */
    private $rowNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="freeSpace", type="integer")
     */
    private $freeSpace;

    /**
     * @var Group
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Group", inversedBy="boxes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $group;

    /**
     * @var ArrayCollection of Tube
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Tube", mappedBy="box")
     */
    private $tubes;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @var User
     *
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="strains")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $createdBy;

    /**
     * @var User
     *
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id")
     */
    private $updatedBy;

    /**
     * Box constructor.
     */
    public function __construct()
    {
        $this->tubes = new ArrayCollection();
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
     * Set name.
     *
     * @param string $name
     *
     * @return Box
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

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Box
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set freezer.
     *
     * @param string $freezer
     *
     * @return Box
     */
    public function setFreezer($freezer)
    {
        $this->freezer = $freezer;

        return $this;
    }

    /**
     * Get freezer.
     *
     * @return string
     */
    public function getFreezer()
    {
        return $this->freezer;
    }

    /**
     * Set location.
     *
     * @param string $location
     *
     * @return Box
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location.
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set colNumber.
     *
     * @param int $colNumber
     *
     * @return Box
     */
    public function setColNumber($colNumber)
    {
        $this->colNumber = $colNumber;

        return $this;
    }

    /**
     * Get colNumber.
     *
     * @return int
     */
    public function getColNumber()
    {
        return $this->colNumber;
    }

    /**
     * Set rowNumber.
     *
     * @param int $rowNumber
     *
     * @return Box
     */
    public function setRowNumber($rowNumber)
    {
        $this->rowNumber = $rowNumber;

        return $this;
    }

    /**
     * Get rowNumber.
     *
     * @return int
     */
    public function getRowNumber()
    {
        return $this->rowNumber;
    }

    /**
     * Set freeSpace.
     *
     * @param int $freeSpace
     *
     * @return Box
     */
    public function setFreeSpace($freeSpace)
    {
        $this->freeSpace = $freeSpace;

        return $this;
    }

    /**
     * Get freeSpace.
     *
     * @return int
     */
    public function getFreeSpace()
    {
        return $this->freeSpace;
    }

    public function tubeAllocation()
    {
        $this->freeSpace = $this->freeSpace - 1;
    }

    /**
     * Set group.
     *
     * @param Group $group
     *
     * @return $this
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group.
     *
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Get tubes.
     *
     * @return ArrayCollection
     */
    public function getTubes()
    {
        return $this->tubes;
    }

    /**
     * Get cell number.
     *
     * @return int
     */
    public function getCellNumber()
    {
        return $this->colNumber * $this->rowNumber;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Get created by.
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Is author ?
     *
     * @param User $user
     *
     * @return bool
     */
    public function isAuthor(User $user)
    {
        return $user === $this->createdBy;
    }

    /**
     * Get updated by.
     *
     * @return User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Is the box empty ?
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->tubes->isEmpty();
    }

    /**
     * Get empty cells.
     *
     * @param null $keepCell
     *
     * @return array
     */
    public function getEmptyCells($keepCell = null)
    {
        $availableLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        $nbCells = $this->colNumber * $this->rowNumber;

        $cellKeys = [];
        for ($i = 0; $i < $this->rowNumber; ++$i) {
            for ($j = 0; $j < $this->colNumber; ++$j) {
                $cellKeys[] = $availableLetters[$i].($j + 1);
            }
        }

        $cellValues = [];
        for ($i = 0; $i < $nbCells; ++$i) {
            $cellValues[] = $i;
        }

        $cells = array_combine($cellKeys, $cellValues);

        foreach ($this->tubes as $tube) {
            $cellName = array_search($tube->getCell(), $cells);

            if ($tube->getCell() !== $keepCell) {
                unset($cells[$cellName]);
            }
        }

        return $cells;
    }

    /**
     * Before persist.
     *
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        // Determine the freeSpace
        $this->freeSpace = $this->colNumber * $this->rowNumber;
    }
}
