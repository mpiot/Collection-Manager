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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Equipment.
 *
 * @ORM\Table(name="equipment")
 * @ORM\Entity(repositoryClass="App\Repository\EquipmentRepository")
 */
class Equipment
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @Gedmo\Slug(fields={"name"}, unique=false)
     * @ORM\Column(name="slug", type="string", length=128)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="serialNumber", type="string", length=255, nullable=true)
     */
    private $serialNumber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="purchaseDate", type="datetime", nullable=true)
     */
    private $purchaseDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Brand")
     */
    private $brand;

    /**
     * @var string
     *
     * @ORM\Column(name="model", type="string", length=255)
     */
    private $model;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Seller")
     */
    private $seller;

    /**
     * @var string
     *
     * @ORM\Column(name="inventoryNumber", type="string", length=255)
     */
    private $inventoryNumber;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Location")
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Group", inversedBy="equipments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $group;

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
     * Set serialNumber.
     *
     * @param string $serialNumber
     */
    public function setSerialNumber($serialNumber): self
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    /**
     * Get serialNumber.
     */
    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    /**
     * Set purchaseDate.
     *
     * @param \DateTime $purchaseDate
     */
    public function setPurchaseDate($purchaseDate): self
    {
        $this->purchaseDate = $purchaseDate;

        return $this;
    }

    /**
     * Get purchaseDate.
     */
    public function getPurchaseDate(): \DateTime
    {
        return $this->purchaseDate;
    }

    /**
     * Set brand.
     */
    public function setBrand(Brand $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get brand.
     */
    public function getBrand(): Brand
    {
        return $this->brand;
    }

    /**
     * Set model.
     *
     * @param string $model
     */
    public function setModel($model): self
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model.
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Set seller.
     */
    public function setSeller(Seller $seller): self
    {
        $this->seller = $seller;

        return $this;
    }

    /**
     * Get seller.
     */
    public function getSeller(): Seller
    {
        return $this->seller;
    }

    /**
     * Set inventoryNumber.
     *
     * @param string $inventoryNumber
     */
    public function setInventoryNumber($inventoryNumber): self
    {
        $this->inventoryNumber = $inventoryNumber;

        return $this;
    }

    /**
     * Get inventoryNumber.
     */
    public function getInventoryNumber(): string
    {
        return $this->inventoryNumber;
    }

    /**
     * Set location.
     */
    public function setLocation(Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location.
     */
    public function getLocation(): Location
    {
        return $this->location;
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
