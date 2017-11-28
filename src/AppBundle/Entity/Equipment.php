<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Equipment.
 *
 * @ORM\Table(name="equipment")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EquipmentRepository")
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Brand")
     */
    private $brand;

    /**
     * @var string
     *
     * @ORM\Column(name="model", type="string", length=255)
     */
    private $model;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Seller")
     */
    private $seller;

    /**
     * @var string
     *
     * @ORM\Column(name="inventoryNumber", type="string", length=255)
     */
    private $inventoryNumber;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Location")
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Group")
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
     * @return Equipment
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
     * Set description.
     *
     * @param string $description
     *
     * @return Equipment
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set serialNumber.
     *
     * @param string $serialNumber
     *
     * @return Equipment
     */
    public function setSerialNumber($serialNumber)
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    /**
     * Get serialNumber.
     *
     * @return string
     */
    public function getSerialNumber()
    {
        return $this->serialNumber;
    }

    /**
     * Set purchaseDate.
     *
     * @param \DateTime $purchaseDate
     *
     * @return Equipment
     */
    public function setPurchaseDate($purchaseDate)
    {
        $this->purchaseDate = $purchaseDate;

        return $this;
    }

    /**
     * Get purchaseDate.
     *
     * @return \DateTime
     */
    public function getPurchaseDate()
    {
        return $this->purchaseDate;
    }

    /**
     * Set brand.
     *
     * @param Brand $brand
     *
     * @return Equipment
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
     * Set model.
     *
     * @param string $model
     *
     * @return Equipment
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model.
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set seller.
     *
     * @param Seller $seller
     *
     * @return Equipment
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
     * Set inventoryNumber.
     *
     * @param string $inventoryNumber
     *
     * @return Equipment
     */
    public function setInventoryNumber($inventoryNumber)
    {
        $this->inventoryNumber = $inventoryNumber;

        return $this;
    }

    /**
     * Get inventoryNumber.
     *
     * @return string
     */
    public function getInventoryNumber()
    {
        return $this->inventoryNumber;
    }

    /**
     * Set location.
     *
     * @param Location $location
     *
     * @return Equipment
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
     * Set group.
     *
     * @param Group $group
     *
     * @return Equipment
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
