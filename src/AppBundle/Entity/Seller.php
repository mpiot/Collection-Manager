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
 * Seller.
 *
 * @ORM\Table(name="seller")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SellerRepository")
 * @UniqueEntity("name")
 * @Vich\Uploadable
 */
class Seller
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
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(name="slug", type="string", length=128, unique=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="offerReference", type="string", length=255, nullable=true)
     */
    private $offerReference;

    /**
     * @Vich\UploadableField(mapping="seller_offer", fileNameProperty="offerName", size="offerSize")
     */
    private $offerFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $offerName;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    private $offerSize;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $offerUpdatedAt;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Product", mappedBy="seller")
     */
    private $products;

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
        $this->products = new ArrayCollection();
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
     * @return Seller
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
     * Set offer reference.
     *
     * @param string $offerReference
     *
     * @return Seller
     */
    public function setOfferReference($offerReference)
    {
        $this->offerReference = $offerReference;

        return $this;
    }

    /**
     * Get offer reference.
     *
     * @return string
     */
    public function getOfferReference()
    {
        return $this->offerReference;
    }

    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     *
     * @return Seller
     */
    public function setOfferFile(File $offerFile = null)
    {
        $this->offerFile = $offerFile;

        if ($offerFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->offerUpdatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    /**
     * @return File|null
     */
    public function getOfferFile()
    {
        return $this->offerFile;
    }

    /**
     * @param string $offerName
     *
     * @return Seller
     */
    public function setOfferName($offerName)
    {
        $this->offerName = $offerName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getOfferName()
    {
        return $this->offerName;
    }

    /**
     * @param int $offerSize
     *
     * @return Seller
     */
    public function setOfferSize($offerSize)
    {
        $this->offerSize = $offerSize;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getOfferSize()
    {
        return $this->offerSize;
    }

    /**
     * @return \Datetime
     */
    public function getOfferUpdatedAt()
    {
        return $this->offerUpdatedAt;
    }

    /**
     * Get products.
     *
     * @return ArrayCollection
     */
    public function getProducts()
    {
        return $this->products;
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
