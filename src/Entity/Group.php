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
 * Group.
 *
 * @ORM\Table(name="`group`")
 * @ORM\Entity(repositoryClass="App\Repository\GroupRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("name", message="A group already exists with this name.")
 */
class Group
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
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(name="slug", length=128, unique=true)
     */
    private $slug;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="administeredGroups")
     * @ORM\JoinTable(name="group_administrators")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\Count(min="1", minMessage = "You must specify at least one administrator.")
     */
    private $administrators;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="groups")
     * @ORM\JoinTable(name="group_members")
     * @ORM\JoinColumn(nullable=false)
     */
    private $members;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Box", mappedBy="group", cascade={"remove"})
     */
    private $boxes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Strain", mappedBy="group", cascade={"remove"})
     */
    private $strains;

    /**
     * @ORM\Column(name="last_strain_number", type="integer", nullable=false)
     */
    private $lastStrainNumber;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Plasmid", mappedBy="group", cascade={"remove"})
     */
    private $plasmids;

    /**
     * @ORM\Column(name="last_plasmid_number", type="integer", nullable=false)
     */
    private $lastPlasmidNumber;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Primer", mappedBy="group", cascade={"remove"})
     */
    private $primers;

    /**
     * @ORM\Column(name="last_primer_number", type="integer", nullable=false)
     */
    private $lastPrimerNumber;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Product", mappedBy="group", cascade={"remove"})
     */
    private $products;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Equipment", mappedBy="group", cascade={"remove"})
     */
    private $equipments;

    /**
     * Group constructor.
     */
    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->administrators = new ArrayCollection();
        $this->boxes = new ArrayCollection();
        $this->types = new ArrayCollection();
        $this->biologicalOriginCategories = new ArrayCollection();
        $this->strains = new ArrayCollection();
        $this->lastStrainNumber = 0;
        $this->plasmids = new ArrayCollection();
        $this->lastPlasmidNumber = 0;
        $this->primers = new ArrayCollection();
        $this->lastPrimerNumber = 0;
        $this->products = new ArrayCollection();
        $this->equipments = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * Get id.
     */
    public function getId(): int
    {
        return $this->id;
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
     * Get slug.
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * Add administrator.
     */
    public function addAdministrator(User $user): self
    {
        $user->addAdministeredGroup($this);
        $this->administrators->add($user);

        // When you add a user as an administrator, add it as a member too
        $this->addMember($user);

        return $this;
    }

    /**
     * Remove administrator.
     */
    public function removeAdministrator(User $user)
    {
        $this->administrators->removeElement($user);
    }

    /**
     * Get administrators.
     */
    public function getAdministrators(): \Doctrine\Common\Collections\Collection
    {
        return $this->administrators;
    }

    /**
     * Is User administrator ?
     */
    public function isAdministrator(User $user): bool
    {
        return $this->administrators->contains($user);
    }

    /**
     * Add member.
     */
    public function addMember(User $user): self
    {
        $user->addGroup($this);
        $this->members->add($user);

        return $this;
    }

    /**
     * Remove member.
     */
    public function removeMember(User $user)
    {
        $this->members->removeElement($user);
    }

    /**
     * Get members.
     */
    public function getMembers(): \Doctrine\Common\Collections\Collection
    {
        return $this->members;
    }

    /**
     * Is User member ?
     */
    public function isMember(User $user): bool
    {
        return $this->members->contains($user);
    }

    /**
     * Add box.
     */
    public function addBox(Box $box): self
    {
        $this->boxes->add($box);

        return $this;
    }

    /**
     * Remove box.
     */
    public function removeBox(Box $box): self
    {
        $this->boxes->removeElement($box);

        return $this;
    }

    /**
     * Get boxes.
     */
    public function getBoxes(): ArrayCollection
    {
        return $this->boxes;
    }

    /**
     * Get strains.
     */
    public function getStrains(): ArrayCollection
    {
        return $this->strains;
    }

    /**
     * Set last strain number.
     *
     *
     * @return $this
     */
    public function setLastStrainNumber(int $number)
    {
        $this->lastStrainNumber = $number;

        return $this;
    }

    /**
     * Get last strain number.
     */
    public function getLastStrainNumber(): int
    {
        return $this->lastStrainNumber;
    }

    /**
     * Get plasmids.
     */
    public function getPlasmids(): ArrayCollection
    {
        return $this->plasmids;
    }

    /**
     * Set last plasmid number.
     *
     *
     * @return $this
     */
    public function setLastPlasmidNumber(int $number)
    {
        $this->lastPlasmidNumber = $number;

        return $this;
    }

    /**
     * Get last plasmid number.
     */
    public function getLastPlasmidNumber(): int
    {
        return $this->lastPlasmidNumber;
    }

    /**
     * Get primers.
     */
    public function getPrimers(): ArrayCollection
    {
        return $this->primers;
    }

    /**
     * Set last primer number.
     *
     *
     * @return $this
     */
    public function setLastPrimerNumber(int $number)
    {
        $this->lastPrimerNumber = $number;

        return $this;
    }

    /**
     * Get last primer number.
     */
    public function getLastPrimerNumber(): int
    {
        return $this->lastPrimerNumber;
    }

    /**
     * Get products.
     */
    public function getProducts(): ArrayCollection
    {
        return $this->products;
    }

    /**
     * Get equipments.
     */
    public function getEquipments(): ArrayCollection
    {
        return $this->equipments;
    }
}
