<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Box.
 *
 * @ORM\Table(name="box")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BoxRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Box
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
     * @var string
     *
     * @ORM\Column(name="boxLetter", type="string", length=255)
     */
    private $boxLetter;

    /**
     * @var int
     *
     * @ORM\Column(name="colNumber", type="integer")
     */
    private $colNumber;

    /**
     * @var int
     *
     * @ORM\Column(name="rowNumber", type="integer")
     */
    private $rowNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="freeSpace", type="integer")
     */
    private $freeSpace;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Project", inversedBy="boxes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @var ArrayCollection of Tube
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Tube", mappedBy="box")
     */
    private $tubes;

    /**
     * @ORM\Column(name="deleted", type="boolean")
     */
    private $deleted;

    /**
     * Box constructor.
     */
    public function __construct()
    {
        $this->tubes = new ArrayCollection();
        $this->deleted = false;
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
     * Set boxLetter.
     *
     * @param string $boxLetter
     *
     * @return Box
     */
    public function setBoxLetter($boxLetter)
    {
        $this->boxLetter = $boxLetter;

        return $this;
    }

    /**
     * Get boxLetter.
     *
     * @return string
     */
    public function getBoxLetter()
    {
        return $this->boxLetter;
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
     * Set project.
     *
     * @param $project
     *
     * @return $this
     */
    public function setProject($project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project.
     *
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
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
     * Set deleted.
     *
     * @param bool $deleted
     *
     * @return $this
     */
    public function setDeleted(bool $deleted)
    {
        $this->deleted = $deleted;

        if (true === $this->deleted) {
            foreach ($this->tubes as $tube) {
                $tube->setDeleted(true);
            }
        }

        return $this;
    }

    /**
     * Get deleted.
     *
     * @return bool
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Is deleted ?
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
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
     * Is it the last box in the project ?
     *
     * @return bool
     */
    public function isLastBox()
    {
        // How many boxes in the project
        $projectBoxesNumber = $this->project->getBoxes()->count();
        $availableLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

        // Get the box number
        $boxNumber = array_search($this->boxLetter, $availableLetters) + 1;

        return $boxNumber === $projectBoxesNumber;
    }

    /**
     * Before persist.
     *
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        // Give a letter to the box
        $projectBoxes = $this->project->getBoxes();

        // Define the new letter for the box
        $availableLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

        $this->boxLetter = $availableLetters[$projectBoxes->count()];

        // Determine the freeSpace
        $this->freeSpace = $this->colNumber * $this->rowNumber;
    }
}
