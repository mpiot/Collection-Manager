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
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Product.
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 * @UniqueEntity({"brandReference", "brand", "group"}, message="A product already exist with the brand reference: {{ value }}.")
 * @UniqueEntity({"sellerReference", "seller", "seller"}, message="A product already exist with the seller reference: {{ value }}.")
 * @Vich\Uploadable
 */
class Product
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Location")
     * @ORM\JoinColumn(nullable=false)
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Brand", inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $brand;

    /**
     * @var string
     *
     * @ORM\Column(name="brandReference", type="string", length=255)
     */
    private $brandReference;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Seller", inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $seller;

    /**
     * @var string
     *
     * @ORM\Column(name="sellerReference", type="string", length=255)
     */
    private $sellerReference;

    /**
     * @var int
     *
     * @ORM\Column(name="catalogPrice", type="integer")
     */
    private $catalogPrice;

    /**
     * @var int
     *
     * @ORM\Column(name="negotiatedPrice", type="integer")
     */
    private $negotiatedPrice;

    /**
     * @Vich\UploadableField(mapping="product_quote", fileNameProperty="quoteName", size="quoteSize")
     */
    private $quoteFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $quoteName;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    private $quoteSize;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $quoteUpdatedAt;

    /**
     * @Vich\UploadableField(mapping="product_manual", fileNameProperty="manualName", size="manualSize")
     */
    private $manualFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $manualName;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    private $manualSize;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $manualUpdatedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="packedBy", type="integer")
     */
    private $packedBy;

    /**
     * @var string
     *
     * @ORM\Column(name="packagingUnit", type="string", length=255)
     */
    private $packagingUnit;

    /**
     * @var string
     *
     * @ORM\Column(name="storageUnit", type="string", length=255)
     */
    private $storageUnit;

    /**
     * @var int
     *
     * @ORM\Column(name="stock", type="integer")
     */
    private $stock;

    /**
     * @var int
     *
     * @ORM\Column(name="stockWarningAlert", type="integer")
     */
    private $stockWarningAlert;

    /**
     * @var int
     *
     * @ORM\Column(name="stockDangerAlert", type="integer")
     */
    private $stockDangerAlert;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Group", inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $group;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductMovement", mappedBy="product", cascade={"remove" })
     */
    private $movements;

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
        $this->stock = 0;
        $this->movements = new ArrayCollection();
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
     * Set brandReference.
     *
     * @param string $brandReference
     */
    public function setBrandReference($brandReference): self
    {
        $this->brandReference = $brandReference;

        return $this;
    }

    /**
     * Get brandReference.
     */
    public function getBrandReference(): string
    {
        return $this->brandReference;
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
     * Set sellerReference.
     *
     * @param string $sellerReference
     */
    public function setSellerReference($sellerReference): self
    {
        $this->sellerReference = $sellerReference;

        return $this;
    }

    /**
     * Get sellerReference.
     */
    public function getSellerReference(): string
    {
        return $this->sellerReference;
    }

    /**
     * Set catalogPrice.
     *
     * @param int $catalogPrice
     */
    public function setCatalogPrice($catalogPrice): self
    {
        $this->catalogPrice = $catalogPrice;

        return $this;
    }

    /**
     * Get catalogPrice.
     */
    public function getCatalogPrice(): int
    {
        return $this->catalogPrice;
    }

    /**
     * Set negotiatedPrice.
     *
     * @param int $negotiatedPrice
     */
    public function setNegotiatedPrice($negotiatedPrice): self
    {
        $this->negotiatedPrice = $negotiatedPrice;

        return $this;
    }

    /**
     * Get negotiatedPrice.
     */
    public function getNegotiatedPrice(): int
    {
        return $this->negotiatedPrice;
    }

    public function setQuoteFile(File $quoteFile = null): self
    {
        $this->quoteFile = $quoteFile;

        if ($quoteFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->quoteUpdatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getQuoteFile(): ?File
    {
        return $this->quoteFile;
    }

    /**
     * @param string $quoteName
     */
    public function setQuoteName($quoteName): self
    {
        $this->quoteName = $quoteName;

        return $this;
    }

    public function getQuoteName(): ?string
    {
        return $this->quoteName;
    }

    public function setQuoteSize($quoteSize): self
    {
        $this->quoteSize = $quoteSize;

        return $this;
    }

    public function getQuoteSize(): ?int
    {
        return $this->quoteSize;
    }

    public function getQuoteUpdatedAt(): \Datetime
    {
        return $this->quoteUpdatedAt;
    }

    public function setManualFile(File $manualFile = null): self
    {
        $this->manualFile = $manualFile;

        if ($manualFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->manualUpdatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getManualFile(): ?File
    {
        return $this->manualFile;
    }

    /**
     * @param string $manualName
     */
    public function setManualName($manualName): self
    {
        $this->manualName = $manualName;

        return $this;
    }

    public function getManualName(): ?string
    {
        return $this->manualName;
    }

    /**
     * @param int $manualSize
     */
    public function setManualSize($manualSize): self
    {
        $this->manualSize = $manualSize;

        return $this;
    }

    public function getManualSize(): ?int
    {
        return $this->manualSize;
    }

    public function getManualUpdatedAt(): \Datetime
    {
        return $this->manualUpdatedAt;
    }

    /**
     * Set packed by.
     *
     * @param string $packedBy
     */
    public function setPackedBy($packedBy): self
    {
        $this->packedBy = $packedBy;

        return $this;
    }

    /**
     * Get packed by.
     */
    public function getPackedBy(): int
    {
        return $this->packedBy;
    }

    /**
     * Set packaging unit.
     *
     * @param string $packagingUnit
     */
    public function setPackagingUnit($packagingUnit): self
    {
        $this->packagingUnit = $packagingUnit;

        return $this;
    }

    /**
     * Get packaging unit.
     */
    public function getPackagingUnit(): string
    {
        return $this->packagingUnit;
    }

    /**
     * Set storageUnit.
     *
     * @param string $storageUnit
     */
    public function setStorageUnit($storageUnit): self
    {
        $this->storageUnit = $storageUnit;

        return $this;
    }

    /**
     * Get storageUnit.
     */
    public function getStorageUnit(): string
    {
        return $this->storageUnit;
    }

    /**
     * Set stock.
     *
     * @param int $stock
     */
    public function setStock($stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * Get stock.
     */
    public function getStock(): int
    {
        return $this->stock;
    }

    /**
     * Set stockWarningAlert.
     *
     * @param int $stockWarningAlert
     */
    public function setStockWarningAlert($stockWarningAlert): self
    {
        $this->stockWarningAlert = $stockWarningAlert;

        return $this;
    }

    /**
     * Get stockWarningAlert.
     */
    public function getStockWarningAlert(): int
    {
        return $this->stockWarningAlert;
    }

    /**
     * Set stockDangerAlert.
     *
     * @param int $stockDangerAlert
     */
    public function setStockDangerAlert($stockDangerAlert): self
    {
        $this->stockDangerAlert = $stockDangerAlert;

        return $this;
    }

    /**
     * Get stockDangerAlert.
     */
    public function getStockDangerAlert(): int
    {
        return $this->stockDangerAlert;
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
     * Get movements.
     */
    public function getMovements(): ArrayCollection
    {
        return $this->movements;
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
