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
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Plasmid.
 *
 * @ORM\Table(name="plasmid")
 * @ORM\Entity(repositoryClass="App\Repository\PlasmidRepository")
 * @ORM\HasLifecycleCallbacks()
 * @Vich\Uploadable
 */
class Plasmid
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Group", inversedBy="plasmids")
     */
    private $group;

    /**
     * @Vich\UploadableField(mapping="plasmid_file", fileNameProperty="genBankName", size="genBankSize")
     */
    private $genBankFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $genBankName;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    private $genBankSize;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $genBankUpdatedAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StrainPlasmid", mappedBy="plasmid", cascade={"remove"})
     */
    private $strainPlasmids;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Primer", inversedBy="plasmids")
     */
    private $primers;

    /**
     * @var ArrayCollection|Tube
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Tube", mappedBy="plasmid", cascade={"persist", "remove"}, orphanRemoval=true)
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
     * Plasmid constructor.
     */
    public function __construct()
    {
        $this->strainPlasmids = new ArrayCollection();
        $this->primers = new ArrayCollection();
        $this->tubes = new ArrayCollection();
    }

    public function __toString()
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
     * Set group.
     *
     * @param Group $group
     */
    public function setGroup($group): self
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

    public function setGenBankFile(File $genBankFile = null): self
    {
        $this->genBankFile = $genBankFile;

        if ($genBankFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->genBankUpdatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getGenBankFile(): ?File
    {
        return $this->genBankFile;
    }

    /**
     * @param string $genBankName
     */
    public function setGenBankName($genBankName): self
    {
        $this->genBankName = $genBankName;

        return $this;
    }

    public function getGenBankName(): ?string
    {
        return $this->genBankName;
    }

    /**
     * @param int $genBankSize
     */
    public function setGenBankSize($genBankSize): self
    {
        $this->genBankSize = $genBankSize;

        return $this;
    }

    public function getGenBankSize(): ?int
    {
        return $this->genBankSize;
    }

    public function getGenBankUpdatedAt(): \Datetime
    {
        return $this->genBankUpdatedAt;
    }

    public function getStrainPlasmids(): ArrayCollection
    {
        return $this->strainPlasmids;
    }

    public function getStrains(): ArrayCollection
    {
        $strains = new ArrayCollection();

        foreach ($this->strainPlasmids as $strainPlasmid) {
            $strains->add($strainPlasmid->getStrain());
        }

        return $strains;
    }

    /**
     * Add primer.
     */
    public function addPrimer(Primer $primer)
    {
        if (!$this->primers->contains($primer)) {
            $this->primers->add($primer);
        }
    }

    /**
     * Remove primer.
     */
    public function removePrimer(Primer $primer)
    {
        if ($this->primers->contains($primer)) {
            $this->primers->removeElement($primer);
        }
    }

    /**
     * Get primers.
     */
    public function getPrimers(): ArrayCollection
    {
        return $this->primers;
    }

    /**
     * Add tube.
     */
    public function addTube(Tube $tube): self
    {
        if (!$this->tubes->contains($tube)) {
            $tube->setPlasmid($this);
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
        $plasmidNumber = $this->getGroup()->getLastPlasmidNumber() + 1;
        $autoName = str_pad($plasmidNumber, 4, '0', STR_PAD_LEFT);

        // Set autoName
        $this->setAutoName($autoName);
        $this->getGroup()->setLastPlasmidNumber($plasmidNumber);
    }
}
