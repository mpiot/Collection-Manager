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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Primer.
 *
 * @ORM\Table(name="primer")
 * @ORM\Entity(repositoryClass="App\Repository\PrimerRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Primer
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
     * @ORM\Column(name="autoName", type="string", length=255)
     */
    private $autoName;

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
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="orientation", type="string", length=255, nullable=true)
     */
    private $orientation;

    /**
     * @var string
     *
     * @ORM\Column(name="sequence", type="string", length=255)
     * @Assert\Regex("/^[ACGTNUKSYMWRBDHV-]+$/i", message="Please, see as the allowed letters in the table on the bottom of the page.")
     */
    private $sequence;

    /**
     * @var string
     *
     * @ORM\Column(name="fivePrimeExtension", type="string", length=255, nullable=true)
     * @Assert\Regex("/^[ACGTNUKSYMWRBDHV-]+$/i", message="Please, see as the allowed letters in the table on the bottom of the page.")
     */
    private $fivePrimeExtension;

    /**
     * @var string
     *
     * @ORM\Column(name="labelMarker", type="string", length=255, nullable=true)
     */
    private $labelMarker;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Group", inversedBy="primers")
     */
    private $group;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Plasmid", mappedBy="primers")
     */
    private $plasmids;

    /**
     * @var string
     *
     * @ORM\Column(name="hybridationTemp", type="float", nullable=true)
     */
    private $hybridationTemp;

    /**
     * @var ArrayCollection|Tube
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Tube", mappedBy="primer", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Assert\Valid
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
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
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
     * Primer constructor.
     */
    public function __construct()
    {
        $this->plasmids = new ArrayCollection();
        $this->tubes = new ArrayCollection();
    }

    /**
     * To string.
     */
    public function __toString(): string
    {
        return $this->autoName.' - '.$this->name;
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
     * Set autoName.
     *
     * @param string $autoName
     */
    public function setAutoName($autoName): self
    {
        $this->autoName = $autoName;

        return $this;
    }

    /**
     * Get autoName.
     */
    public function getAutoName(): string
    {
        return $this->autoName;
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
     * Set orientation.
     *
     * @param string $orientation
     */
    public function setOrientation($orientation): self
    {
        $this->orientation = $orientation;

        return $this;
    }

    /**
     * Get orientation.
     */
    public function getOrientation(): string
    {
        return $this->orientation;
    }

    /**
     * Set sequence.
     *
     * @param string $sequence
     */
    public function setSequence($sequence): self
    {
        $this->sequence = mb_strtoupper($sequence);

        return $this;
    }

    /**
     * Get sequence.
     */
    public function getSequence(): string
    {
        return $this->sequence;
    }

    /**
     * Set fivePrimeExtension.
     *
     * @param string $fivePrimeExtension
     */
    public function setFivePrimeExtension($fivePrimeExtension): self
    {
        $this->fivePrimeExtension = mb_strtoupper($fivePrimeExtension);

        return $this;
    }

    /**
     * Get fivePrimeExtension.
     */
    public function getFivePrimeExtension(): string
    {
        return $this->fivePrimeExtension;
    }

    /**
     * Set LabelMarker.
     *
     * @param string $labelMarker
     */
    public function setLabelMarker($labelMarker): self
    {
        $this->labelMarker = $labelMarker;

        return $this;
    }

    /**
     * Get LabelMarker.
     */
    public function getLabelMarker(): string
    {
        return $this->labelMarker;
    }

    /**
     * Set Hybridation Temperature.
     *
     * @param string $hybridationTemp
     */
    public function setHybridationTemp($hybridationTemp): self
    {
        $this->hybridationTemp = $hybridationTemp;

        return $this;
    }

    /**
     * Get Hybridation Temperature.
     */
    public function getHybridationTemp(): string
    {
        return $this->hybridationTemp;
    }

    /**
     * Set group.
     */
    public function setGroup(Group $group): self
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
     * Get plasmids.
     */
    public function getPlasmids(): ArrayCollection
    {
        return $this->plasmids;
    }

    /**
     * Add tube.
     */
    public function addTube(Tube $tube): self
    {
        if (!$this->tubes->contains($tube)) {
            $tube->setPrimer($this);
            $this->tubes->add($tube);
        }

        return $this;
    }

    /**
     * Remove tube.
     *
     *
     * @return $this
     */
    public function removeTube(Tube $tube)
    {
        if ($this->tubes->contains($tube)) {
            $this->tubes->removeElement($tube);
        }

        return $this;
    }

    /**
     * Get tubes.
     *
     * @return Tube|ArrayCollection
     */
    public function getTubes()
    {
        return $this->tubes;
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
     */
    public function prePersist()
    {
        $primerNumber = $this->getGroup()->getLastPrimerNumber() + 1;
        $autoName = str_pad($primerNumber, 4, '0', STR_PAD_LEFT);

        // Set autoName
        $this->setAutoName($autoName);
        $this->getGroup()->setLastPrimerNumber($primerNumber);
    }
}
