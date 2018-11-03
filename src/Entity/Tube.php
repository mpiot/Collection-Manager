<?php

/*
 * Copyright 2016-2018 Mathieu Piot.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Tube.
 *
 * @ORM\Table(name="tube")
 * @ORM\Entity(repositoryClass="App\Repository\TubeRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity({"cell", "box"}, message="You can't have many tubes in the same cell.")
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Box", inversedBy="tubes")
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Strain", inversedBy="tubes")
     * @ORM\JoinColumn(nullable=true)
     */
    private $strain;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Plasmid", inversedBy="tubes")
     * @ORM\JoinColumn(nullable=true)
     */
    private $plasmid;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Primer", inversedBy="tubes")
     * @ORM\JoinColumn(nullable=true)
     */
    private $primer;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="strains")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $createdBy;

    /**
     * @var User
     *
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id")
     */
    private $updatedBy;

    /**
     * Clone.
     *
     * Used when a strain is cloned.
     */
    public function __clone()
    {
        $this->id = null;
        $this->strain = null;
        $this->cell = null;
        $this->cellName = null;

        // If the box is full, set box on null
        if (0 === $this->box->getFreeSpace()) {
            $this->box = null;
        }
    }

    /**
     * Get id.
     */
    public function getId(): int
    {
        return $this->id;
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

    public function setStrain(Strain $strain)
    {
        $this->strain = $strain;

        return $this;
    }

    public function getStrain()
    {
        return $this->strain;
    }

    public function setPlasmid(Plasmid $plasmid)
    {
        $this->plasmid = $plasmid;

        return $this;
    }

    public function getPlasmid()
    {
        return $this->plasmid;
    }

    public function setPrimer(Primer $primer)
    {
        $this->primer = $primer;

        return $this;
    }

    public function getPrimer()
    {
        return $this->primer;
    }

    public function getContent()
    {
        if (null !== $this->strain) {
            return $this->strain;
        } elseif (null !== $this->plasmid) {
            return $this->plasmid;
        }

        return $this->primer;
    }

    public function getContentDiscr()
    {
        if (null !== $this->strain) {
            return 'strain';
        } elseif (null !== $this->plasmid) {
            return 'plasmid';
        }

        return 'primer';
    }

    /**
     * Get created.
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * Get updated.
     */
    public function getUpdated(): \DateTime
    {
        return $this->updated;
    }

    /**
     * Get created by.
     */
    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    /**
     * Is author ?
     */
    public function isAuthor(User $user): bool
    {
        return $user === $this->createdBy;
    }

    /**
     * Get updated by.
     */
    public function getUpdatedBy(): User
    {
        return $this->updatedBy;
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
