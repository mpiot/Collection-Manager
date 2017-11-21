<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Seller.
 *
 * @ORM\Table(name="seller")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SellerRepository")
 * @UniqueEntity("name")
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
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\File", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
     */
    private $offerFile;
    private $addOfferFile = false;

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
     * Set offer file.
     *
     * @param File $offerFile
     *
     * @return Seller
     */
    public function setOfferFile(File $offerFile = null)
    {
        $this->offerFile = $offerFile;

        return $this;
    }

    /**
     * Get offer file.
     *
     * @return File
     */
    public function getOfferFile()
    {
        return $this->offerFile;
    }

    /**
     * Set add offer file.
     *
     * @param $addOfferFile
     *
     * @return $this
     */
    public function setAddOfferFile(bool $addOfferFile)
    {
        $this->addOfferFile = $addOfferFile;

        return $this;
    }

    /**
     * Get add offer file.
     *
     * @return bool
     */
    public function getAddOfferFile()
    {
        return $this->addOfferFile;
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
