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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\GmoStrain", inversedBy="tubes")
     */
    private $gmoStrain;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\WildStrain", inversedBy="tubes")
     */
    private $wildStrain;

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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

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

    public function setGmoStrain(GmoStrain $strain)
    {
        $this->gmoStrain = $strain;

        return $this;
    }

    public function getGmoStrain()
    {
        return $this->gmoStrain;
    }

    public function setWildStrain(WildStrain $strain)
    {
        $this->wildStrain = $strain;

        return $this;
    }

    public function getWildStrain()
    {
        return $this->wildStrain;
    }

    public function getStrain()
    {
        if (null !== $this->gmoStrain) {
            return $this->getGmoStrain();
        } else {
            return $this->getWildStrain();
        }
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

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
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
     */
    public function prePersist()
    {
        // Give a name to the tube
        // The name is composed like this:
        // ProjectPrefix_BoxLetter_xxxType

        // ProjectPrefix (The prefix of the first Tube)
        $projectPrefix = $this->getBox()->getProject()->getPrefix();

        // BoxLetter (idem, the first tube)
        $boxLetter = $this->getBox()->getBoxLetter();

        // In array the first cell is 0, in real box, it's 1
        $cell = $this->cell + 1;

        // Adapt the boxCell like: 1 => 001, 10 => 010, 100 => 100, never more than 999
        if ($cell < 10) {
            $boxCell = '00'.$cell;
        } elseif ($cell > 99) {
            $boxCell = $cell;
        } else {
            $boxCell = '0'.$cell;
        }

        // Type Letter
        if (null !== $this->getGmoStrain()) {
            $lastLetter = $this->getGmoStrain()->getType()->getLetter();
        } else {
            $lastLetter = $this->getWildStrain()->getType()->getLetter();
        }

        // Generate the tube name
        $this->name = $projectPrefix.'_'.$boxLetter.$boxCell.$lastLetter;
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

        $cellsName = [];
        for ($i = 0; $i < $this->box->getRowNumber(); ++$i) {
            for ($j = 0; $j < $this->box->getColNumber(); ++$j) {
                $cellsName[] = $availableLetters[$i].($j + 1);
            }
        }

        $this->cellName = $cellsName[$this->cell];
    }
}
