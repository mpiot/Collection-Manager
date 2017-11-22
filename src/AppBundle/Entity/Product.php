<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Product.
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProductRepository")
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Location")
     * @ORM\JoinColumn(nullable=false)
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Brand", inversedBy="products")
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Seller", inversedBy="products")
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Group")
     * @ORM\JoinColumn(nullable=false)
     */
    private $group;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ProductMovement", mappedBy="product")
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="strains")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $createdBy;

    /**
     * @var User
     *
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
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
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set location.
     *
     * @param Location $location
     *
     * @return Product
     */
    public function setLocation(Location $location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location.
     *
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set brand.
     *
     * @param Brand $brand
     *
     * @return Product
     */
    public function setBrand(Brand $brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get brand.
     *
     * @return Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Set brandReference.
     *
     * @param string $brandReference
     *
     * @return Product
     */
    public function setBrandReference($brandReference)
    {
        $this->brandReference = $brandReference;

        return $this;
    }

    /**
     * Get brandReference.
     *
     * @return string
     */
    public function getBrandReference()
    {
        return $this->brandReference;
    }

    /**
     * Set seller.
     *
     * @param Seller $seller
     *
     * @return Product
     */
    public function setSeller(Seller $seller)
    {
        $this->seller = $seller;

        return $this;
    }

    /**
     * Get seller.
     *
     * @return Seller
     */
    public function getSeller()
    {
        return $this->seller;
    }

    /**
     * Set sellerReference.
     *
     * @param string $sellerReference
     *
     * @return Product
     */
    public function setSellerReference($sellerReference)
    {
        $this->sellerReference = $sellerReference;

        return $this;
    }

    /**
     * Get sellerReference.
     *
     * @return string
     */
    public function getSellerReference()
    {
        return $this->sellerReference;
    }

    /**
     * Set catalogPrice.
     *
     * @param int $catalogPrice
     *
     * @return Product
     */
    public function setCatalogPrice($catalogPrice)
    {
        $this->catalogPrice = $catalogPrice;

        return $this;
    }

    /**
     * Get catalogPrice.
     *
     * @return int
     */
    public function getCatalogPrice()
    {
        return $this->catalogPrice;
    }

    /**
     * Set negotiatedPrice.
     *
     * @param int $negotiatedPrice
     *
     * @return Product
     */
    public function setNegotiatedPrice($negotiatedPrice)
    {
        $this->negotiatedPrice = $negotiatedPrice;

        return $this;
    }

    /**
     * Get negotiatedPrice.
     *
     * @return int
     */
    public function getNegotiatedPrice()
    {
        return $this->negotiatedPrice;
    }

    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     *
     * @return Product
     */
    public function setQuoteFile(File $quoteFile = null)
    {
        $this->quoteFile = $quoteFile;

        if ($quoteFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->quoteUpdatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    /**
     * @return File|null
     */
    public function getQuoteFile()
    {
        return $this->quoteFile;
    }

    /**
     * @param string $quoteName
     *
     * @return Product
     */
    public function setQuoteName($quoteName)
    {
        $this->quoteName = $quoteName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getQuoteName()
    {
        return $this->quoteName;
    }

    /**
     * @param int $genBankSize
     *
     * @return Product
     */
    public function setQuoteSize($quoteSize)
    {
        $this->quoteSize = $quoteSize;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getQuoteSize()
    {
        return $this->quoteSize;
    }

    /**
     * @return \Datetime
     */
    public function getQuoteUpdatedAt()
    {
        return $this->quoteUpdatedAt;
    }

    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     *
     * @return Product
     */
    public function setManualFile(File $manualFile = null)
    {
        $this->manualFile = $manualFile;

        if ($manualFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->manualUpdatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    /**
     * @return File|null
     */
    public function getManualFile()
    {
        return $this->manualFile;
    }

    /**
     * @param string $manualName
     *
     * @return Product
     */
    public function setManualName($manualName)
    {
        $this->manualName = $manualName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getManualName()
    {
        return $this->manualName;
    }

    /**
     * @param int $manualSize
     *
     * @return Product
     */
    public function setManualSize($manualSize)
    {
        $this->manualSize = $manualSize;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getManualSize()
    {
        return $this->manualSize;
    }

    /**
     * @return \Datetime
     */
    public function getManualUpdatedAt()
    {
        return $this->manualUpdatedAt;
    }

    /**
     * Set packed by.
     *
     * @param string $packedBy
     *
     * @return Product
     */
    public function setPackedBy($packedBy)
    {
        $this->packedBy = $packedBy;

        return $this;
    }

    /**
     * Get packed by.
     *
     * @return int
     */
    public function getPackedBy()
    {
        return $this->packedBy;
    }

    /**
     * Set packaging unit.
     *
     * @param string $packagingUnit
     *
     * @return Product
     */
    public function setPackagingUnit($packagingUnit)
    {
        $this->packagingUnit = $packagingUnit;

        return $this;
    }

    /**
     * Get packaging unit.
     *
     * @return string
     */
    public function getPackagingUnit()
    {
        return $this->packagingUnit;
    }

    /**
     * Set storageUnit.
     *
     * @param string $storageUnit
     *
     * @return Product
     */
    public function setStorageUnit($storageUnit)
    {
        $this->storageUnit = $storageUnit;

        return $this;
    }

    /**
     * Get storageUnit.
     *
     * @return string
     */
    public function getStorageUnit()
    {
        return $this->storageUnit;
    }

    /**
     * Set stock.
     *
     * @param int $stock
     *
     * @return Product
     */
    public function setStock($stock)
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * Get stock.
     *
     * @return int
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * Set stockWarningAlert.
     *
     * @param int $stockWarningAlert
     *
     * @return Product
     */
    public function setStockWarningAlert($stockWarningAlert)
    {
        $this->stockWarningAlert = $stockWarningAlert;

        return $this;
    }

    /**
     * Get stockWarningAlert.
     *
     * @return int
     */
    public function getStockWarningAlert()
    {
        return $this->stockWarningAlert;
    }

    /**
     * Set stockDangerAlert.
     *
     * @param int $stockDangerAlert
     *
     * @return Product
     */
    public function setStockDangerAlert($stockDangerAlert)
    {
        $this->stockDangerAlert = $stockDangerAlert;

        return $this;
    }

    /**
     * Get stockDangerAlert.
     *
     * @return int
     */
    public function getStockDangerAlert()
    {
        return $this->stockDangerAlert;
    }

    /**
     * Set group.
     *
     * @param Group $group
     *
     * @return Product
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group.
     *
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Get movements.
     *
     * @return ArrayCollection
     */
    public function getMovements()
    {
        return $this->movements;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Get created by.
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Is author ?
     *
     * @param User $user
     *
     * @return bool
     */
    public function isAuthor(User $user)
    {
        return $user === $this->createdBy;
    }

    /**
     * Get updated by.
     *
     * @return User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }
}
