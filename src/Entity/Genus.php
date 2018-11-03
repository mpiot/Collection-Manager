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
 * Genus.
 *
 * @ORM\Table(name="genus")
 * @ORM\Entity(repositoryClass="App\Repository\GenusRepository")
 */
class Genus
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
     * @Assert\Regex("#^[A-Z][a-z]*$#", message="The genus begin with a capital letter. (eg: Saccharomyces)")
     */
    private $name;

    /**
     * @var \stdClass
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Species", mappedBy="genus")
     */
    private $species;

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

    public function __construct()
    {
        $this->species = new ArrayCollection();
    }

    /**
     * Get id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set genus.
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get genus.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set species.
     *
     * @param \stdClass $species
     */
    public function setSpecies(Species $species): self
    {
        $this->species = $species;

        return $this;
    }

    /**
     * Get species.
     */
    public function getSpecies(): ArrayCollection
    {
        return $this->species;
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
}
