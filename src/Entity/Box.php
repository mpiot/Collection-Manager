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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Box.
 *
 * @ORM\Table(name="box")
 * @ORM\Entity(repositoryClass="App\Repository\BoxRepository")
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
     * @Gedmo\Slug(fields={"name"}, unique=false)
     * @ORM\Column(name="slug", type="string", length=128)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
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
     * @Assert\NotBlank()
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Group", inversedBy="boxes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $group;

    /**
     * @var ArrayCollection of Tube
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Tube", mappedBy="box", cascade={"remove"})
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
     * Box constructor.
     */
    public function __construct()
    {
        $this->tubes = new ArrayCollection();
    }

    /**
     * Get id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get slug.
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * Set name.
     *
     * @param string $name
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set description.
     *
     * @param string $description
     */
    public function setDescription($description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set freezer.
     *
     * @param string $freezer
     */
    public function setFreezer($freezer): self
    {
        $this->freezer = $freezer;

        return $this;
    }

    /**
     * Get freezer.
     */
    public function getFreezer(): string
    {
        return $this->freezer;
    }

    /**
     * Set location.
     *
     * @param string $location
     */
    public function setLocation($location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location.
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * Set colNumber.
     *
     * @param int $colNumber
     */
    public function setColNumber($colNumber): self
    {
        $this->colNumber = $colNumber;

        return $this;
    }

    /**
     * Get colNumber.
     */
    public function getColNumber(): int
    {
        return $this->colNumber;
    }

    /**
     * Set rowNumber.
     *
     * @param int $rowNumber
     */
    public function setRowNumber($rowNumber): self
    {
        $this->rowNumber = $rowNumber;

        return $this;
    }

    /**
     * Get rowNumber.
     */
    public function getRowNumber(): int
    {
        return $this->rowNumber;
    }

    /**
     * Set freeSpace.
     *
     * @param int $freeSpace
     */
    public function setFreeSpace($freeSpace): self
    {
        $this->freeSpace = $freeSpace;

        return $this;
    }

    /**
     * Get freeSpace.
     */
    public function getFreeSpace(): int
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
     */
    public function getGroup(): Group
    {
        return $this->group;
    }

    /**
     * Get tubes.
     */
    public function getTubes(): ArrayCollection
    {
        return $this->tubes;
    }

    /**
     * Get cell number.
     */
    public function getCellNumber(): int
    {
        return $this->colNumber * $this->rowNumber;
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
     * Is the box empty ?
     */
    public function isEmpty(): bool
    {
        return $this->tubes->isEmpty();
    }

    /**
     * Get empty cells.
     */
    public function getEmptyCells($keepCell = null): array
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
            $cellName = array_search($tube->getCell(), $cells, true);

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
