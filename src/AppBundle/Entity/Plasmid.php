<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Plasmid.
 *
 * @ORM\Table(name="plasmid")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PlasmidRepository")
 * @ORM\HasLifeCycleCallbacks()
 * @UniqueEntity({"name", "team"}, message="This name is already used by another plasmid.")
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
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(name="slug", type="string", length=128, unique=true)
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
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Team", inversedBy="plasmids")
     */
    private $team;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\GenBankFile", mappedBy="plasmid", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
     */
    private $genBankFile;

    private $addGenBankFile = false;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\StrainPlasmid", mappedBy="plasmid")
     */
    private $strainPlasmids;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Primer", inversedBy="plasmids")
     */
    private $primers;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="last_editor", nullable=true)
     */
    private $lastEditor;

    /**
     * @ORM\Column(name="last_edit", type="datetime", nullable=true)
     */
    private $lastEdit;

    /**
     * Plasmid constructor.
     */
    public function __construct()
    {
        $this->strainPlasmids = new ArrayCollection();
        $this->primers = new ArrayCollection();
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
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set autoName.
     *
     * @param string $autoName
     *
     * @return Plasmid
     */
    public function setAutoName($autoName)
    {
        $this->autoName = $autoName;

        return $this;
    }

    /**
     * Get autoName.
     *
     * @return string
     */
    public function getAutoName()
    {
        return $this->autoName;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Plasmid
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
     * Set team.
     *
     * @param Team $team
     *
     * @return Plasmid
     */
    public function setTeam($team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team.
     *
     * @return Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set genBank file.
     *
     * @param string $genBankFile
     *
     * @return Plasmid
     */
    public function setGenBankFile($genBankFile)
    {
        $this->genBankFile = $genBankFile;

        return $this;
    }

    /**
     * Get genBank file.
     *
     * @return string
     */
    public function getGenBankFile()
    {
        return $this->genBankFile;
    }

    public function hasGenBankFile()
    {
        return (null !== $this->genBankFile) ? true : false;
    }

    /**
     * @param $addManual
     *
     * @return $this
     */
    public function setAddGenBankFile($addGenBankFile)
    {
        $this->addGenBankFile = $addGenBankFile;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddGenBankFile()
    {
        return $this->addGenBankFile;
    }

    /**
     * @return ArrayCollection
     */
    public function getStrainPlasmids()
    {
        return $this->strainPlasmids;
    }

    /**
     * @return ArrayCollection
     */
    public function getStrains()
    {
        $strains = new ArrayCollection();

        foreach ($this->strainPlasmids as $strainPlasmid) {
            $strains->add($strainPlasmid->getStrain());
        }

        return $strains;
    }

    /**
     * Add primer.
     *
     * @param Primer $primer
     */
    public function addPrimer(Primer $primer)
    {
        $this->primers->add($primer);
    }

    /**
     * Remove primer.
     *
     * @param Primer $primer
     */
    public function removePrimer(Primer $primer)
    {
        $this->primers->removeElement($primer);
    }

    /**
     * Get primers.
     *
     * @return ArrayCollection
     */
    public function getPrimers()
    {
        return $this->primers;
    }

    /**
     * Set author.
     *
     * @param User $user
     *
     * @return $this
     */
    public function setAuthor(User $user)
    {
        $this->author = $user;

        return $this;
    }

    /**
     * Get author.
     *
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set lastEditor.
     *
     * @param User $user
     *
     * @return $this
     */
    public function setLastEditor(User $user)
    {
        $this->lastEditor = $user;

        return $this;
    }

    /**
     * Get lastEditor.
     *
     * @return User
     */
    public function getLastEditor()
    {
        return $this->lastEditor;
    }

    /**
     * Set lastEdit.
     *
     * @param \DateTime $lastEdit
     *
     * @return $this
     */
    public function setLastEdit(\DateTime $lastEdit)
    {
        $this->lastEdit = $lastEdit;

        return $this;
    }

    /**
     * Get lastEdit.
     *
     * @return mixed
     */
    public function getLastEdit()
    {
        return $this->lastEdit;
    }
}
