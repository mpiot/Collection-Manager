<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Type.
 *
 * @ORM\Table(name="tube")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TubeRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Tube
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Strain", inversedBy="tubes")
     */
    private $strain;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Project")
     */
    private $project;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Box", inversedBy="tubes")
     */
    private $box;

    /**
     * @var int
     *
     * @ORM\Column(name="cell", type="integer")
     */
    private $cell;

    /**
     * @var int
     *
     * @ORM\Column(name="cellName", type="string", length=255)
     */
    private $cellName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deletionDate", type="datetime", nullable=true)
     */
    private $deletionDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="deleted", type="boolean")
     */
    private $deleted;

    public function __construct()
    {
        $this->deleted = false;
        $this->creationDate = new \DateTime();
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

    public function setStrain(Strain $strain)
    {
        $this->strain = $strain;

        return $this;
    }

    public function getStrain()
    {
        return $this->strain;
    }

    public function setProject(Project $project)
    {
        $this->project = $project;

        return $this;
    }

    public function getProject()
    {
        return $this->project;
    }

    public function setBox(Box $box)
    {
        // If the actualbox is null, it's a new tube
        if (null === $this->box) {
            // Then we remove one space in the new box
            $box->setFreeSpace($box->getFreeSpace() - 1);
        // Else if the actual box is not null and different to the new box
        } elseif (null !== $this->box && $this->box !== $box) {
            // Then we add a free space in the previous box, and remove on in the new box
            $box->setFreeSpace($box->getFreeSpace() - 1);
            $this->box->setFreeSpace($this->box->getFreeSpace() + 1);
        }

        $this->box = $box;

        return $this;
    }

    public function getBox()
    {
        return $this->box;
    }

    public function setCell($cell)
    {
        $this->cell = $cell;

        return $this;
    }

    public function getCell()
    {
        return $this->cell;
    }

    public function setCellName($cellName)
    {
        $this->cellName = $cellName;
    }

    public function getCellName()
    {
        return $this->cellName;
    }

    public function setCreationDate(\DateTime $date)
    {
        $this->creationDate = $date;
    }

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function setDeletionDate(\DateTime $date)
    {
        $this->deletionDate = $date;
    }

    public function getDeletionDate()
    {
        return $this->deletionDate;
    }

    public function setDeleted(bool $deleted)
    {
        if (true === $deleted && false === $this->deleted) {
            $this->deletionDate = new \DateTime();
        } elseif (false === $deleted && true === $this->deleted) {
            $this->deletionDate = null;
        }

        $this->deleted = $deleted;
    }

    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Before persist.
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function autoCellName()
    {
        $availableLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

        $rowNumber = $this->box->getRowNumber();
        $colNumber = $this->box->getColNumber();
        $cellsName = [];

        for ($i = 0; $i < $rowNumber; ++$i) {
            for ($j = 0; $j < $colNumber; ++$j) {
                $cellsName[] = $availableLetters[$i].($j + 1);
            }
        }

        $this->cellName = $cellsName[$this->cell];
    }
}
