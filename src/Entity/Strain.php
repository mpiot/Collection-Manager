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
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Strain.
 *
 * @ORM\Table(name="strain")
 * @ORM\Entity(repositoryClass="App\Repository\StrainRepository")
 * @UniqueEntity({"uniqueCode", "group"}, message="A strain already exist with the unique code: {{ value }}.")
 * @UniqueEntity({"autoName", "group"}, message="A strain already exist with the auto name: {{ value }}.")
 * @ORM\HasLifecycleCallbacks()
 */
class Strain
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
     * @ORM\Column(name="discriminator", type="string", length=255)
     * @Assert\Regex(
     *     pattern="/^(gmo|wild)$/",
     *     message="The discriminator must be 'gmo' or 'wild'."
     * )
     */
    private $discriminator;

    /**
     * @var string
     *
     * @ORM\Column(name="auto_name", type="string", length=255)
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
     * @var string
     *
     * @ORM\Column(name="unique_code", type="string", length=255, nullable=true)
     */
    private $uniqueCode;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var bool
     *
     * @ORM\Column(name="sequenced", type="boolean")
     * @Assert\Type("bool")
     */
    private $sequenced;

    /**
     * @var Species
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Species", inversedBy="strains")
     * @ORM\JoinColumn(name="species", nullable=true)
     */
    private $species;

    /**
     * @var string
     *
     * @ORM\Column(name="genotype", type="text", nullable=true)
     */
    private $genotype;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var ArrayCollection|Tube
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Tube", mappedBy="strain", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Assert\Valid
     */
    private $tubes;

    /**
     * @var ArrayCollection|StrainPlasmid
     *
     * @ORM\OneToMany(targetEntity="App\Entity\StrainPlasmid", mappedBy="strain", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Assert\Valid()
     */
    private $strainPlasmids;

    /**
     * @var ArrayCollection|Strain
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Strain", inversedBy="children")
     * @ORM\JoinTable(name="strains_parents")
     */
    private $parents;

    /**
     * @var ArrayCollection|Strain
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Strain", mappedBy="parents")
     */
    private $children;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     * @Assert\Country()
     * @Assert\Expression(
     *     "(null !== this.getAddress() and null !== this.getCountry()) or null === this.getAddress()",
     *     message="If the address is set, the country must be set to."
     * )
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="biologicalOrigin", type="string", length=255, nullable=true)
     * @Assert\Expression(
     *     "null !== this.getBiologicalOrigin() or 'gmo' === this.getDiscriminator()",
     *     message="In Wild strain, the biological origin is required."
     * )
     */
    private $biologicalOrigin;

    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=255, nullable=true)
     */
    private $source;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="float", nullable=true)
     * @Assert\Expression(
     *     "(null !== this.getLatitude() && null !== this.getLongitude()) or (null === this.getLatitude() && null === this.getLongitude())",
     *     message="Both: latitude and longitude must be fill or not."
     * )
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="float", nullable=true)
     * @Assert\Expression(
     *     "(null !== this.getLatitude() && null !== this.getLongitude()) or (null === this.getLatitude() && null === this.getLongitude())",
     *     message="Both: latitude and longitude must be fill or not."
     * )
     */
    private $longitude;

    /**
     * @var Group
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Group", inversedBy="strains")
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

    public function __construct()
    {
        $this->tubes = new ArrayCollection();
        $this->strainPlasmids = new ArrayCollection();
        $this->parents = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getAutoName().' - '.$this->name;
    }

    /**
     * Clone.
     *
     * Used to create a new Strain on an other strain base.
     */
    public function __clone()
    {
        $this->id = null;
        $this->slug = null;
        $this->autoName = null;
        foreach ($this->tubes as $key => $tube) {
            $this->tubes[$key] = clone $tube;
            $this->tubes[$key]->setStrain($this);
        }
        $this->createdBy = null;
        $this->created = null;
        $this->updatedBy = null;
        $this->updated = null;
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
     * Set discriminator.
     *
     *
     * @return $this
     */
    public function setDiscriminator(string $discriminator)
    {
        $this->discriminator = $discriminator;

        return $this;
    }

    /**
     * Get discriminator.
     */
    public function getDiscriminator(): string
    {
        return $this->discriminator;
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
     * Set unique code.
     *
     * @param $uniqueCode
     *
     * @return $this
     */
    public function setUniqueCode($uniqueCode)
    {
        $this->uniqueCode = $uniqueCode;

        return $this;
    }

    /**
     * Get unique code.
     */
    public function getUniqueCode(): string
    {
        return $this->uniqueCode;
    }

    /**
     * Get full name.
     */
    public function getFullName(): string
    {
        return $this->autoName.' - '.$this->name;
    }

    /**
     * Set comment.
     *
     * @param string $comment
     */
    public function setComment($comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * Set sequenced.
     *
     * @param bool $sequenced
     */
    public function setSequenced($sequenced): self
    {
        $this->sequenced = $sequenced;

        return $this;
    }

    /**
     * Get sequenced.
     */
    public function getSequenced(): bool
    {
        return $this->sequenced;
    }

    /**
     * Set species.
     *
     *
     * @return $this
     */
    public function setSpecies(Species $species)
    {
        $this->species = $species;

        return $this;
    }

    /**
     * Get species.
     */
    public function getSpecies(): Species
    {
        return $this->species;
    }

    /**
     * Set genotype.
     *
     * @param string $genotype
     */
    public function setGenotype($genotype): self
    {
        $this->genotype = $genotype;

        return $this;
    }

    /**
     * Get genotype.
     */
    public function getGenotype(): string
    {
        return $this->genotype;
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
     * Add tube.
     */
    public function addTube(Tube $tube): self
    {
        if (!$this->tubes->contains($tube)) {
            $tube->setStrain($this);
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
     * Get allowed users.
     */
    public function getAllowedUsers(): array
    {
        $users = $this->group->getMembers()->toArray();

        return $users;
    }

    /**
     * Is allowed user ?
     */
    public function isAllowedUser(User $user): bool
    {
        return \in_array($user, $this->getAllowedUsers(), true);
    }

    /**
     * Add strainPlasmid.
     *
     *
     * @return $this
     */
    public function addStrainPlasmid(StrainPlasmid $strainPlasmid)
    {
        $strainPlasmid->setStrain($this);
        $this->strainPlasmids->add($strainPlasmid);

        return $this;
    }

    /**
     * Remove strainPlasmid.
     *
     *
     * @return $this
     */
    public function removeStrainPlasmid(StrainPlasmid $strainPlasmid)
    {
        $this->strainPlasmids->removeElement($strainPlasmid);

        return $this;
    }

    /**
     * Get strainPlasmids.
     *
     * @return StrainPlasmid|ArrayCollection
     */
    public function getStrainPlasmids()
    {
        return $this->strainPlasmids;
    }

    /**
     * Add parent.
     *
     * @param Strain $strain
     */
    public function addParent(self $strain): self
    {
        $this->parents->add($strain);

        return $this;
    }

    /**
     * Remove parent.
     *
     * @param Strain $strain
     */
    public function removeParent(self $strain): self
    {
        $this->parents->removeElement($strain);

        return $this;
    }

    /**
     * Get parent.
     *
     * @return Strain|ArrayCollection
     */
    public function getParents()
    {
        return $this->parents;
    }

    /**
     * Get children.
     *
     * @return Strain|ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set address.
     *
     * @param string $address
     */
    public function setAddress($address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address.
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Set country.
     *
     * @param string $country
     */
    public function setCountry($country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country.
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * Set biologicalOrigin.
     *
     * @param string $biologicalOrigin
     */
    public function setBiologicalOrigin($biologicalOrigin): self
    {
        $this->biologicalOrigin = $biologicalOrigin;

        return $this;
    }

    /**
     * Get biologicalOrigin.
     */
    public function getBiologicalOrigin(): string
    {
        return $this->biologicalOrigin;
    }

    /**
     * Set source.
     *
     * @param string $source
     */
    public function setSource($source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source.
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Set latitude.
     *
     * @param $latitude
     *
     * @return $this
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude.
     */
    public function getLatitude(): string
    {
        return $this->latitude;
    }

    /**
     * Set longitude.
     *
     * @param $longitude
     *
     * @return $this
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude.
     */
    public function getLongitude(): string
    {
        return $this->longitude;
    }

    /**
     * Set group.
     *
     *
     * @return $this
     */
    public function setGroup(Group $group)
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

    /**
     * Set main Species.
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function lifeCycleMainSpecies()
    {
        // Define the main Species
        $species = $this->getSpecies();

        if (null !== $species && !$species->isMainSpecies()) {
            $this->setSpecies($species->getMainSpecies());
        }
    }

    /**
     * Set autoName.
     *
     * @ORM\PrePersist()
     */
    public function lifeCycleAutoName()
    {
        $strainNumber = $this->getGroup()->getLastStrainNumber() + 1;
        $autoName = str_pad($strainNumber, 4, '0', STR_PAD_LEFT);

        // Set autoName
        $this->setAutoName($autoName);
        $this->group->setLastStrainNumber($strainNumber);
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        // Somme fields are specifics for GMO and other for Wild
        if ('gmo' === $this->discriminator) {
            if (null !== $this->biologicalOrigin) {
                $context->buildViolation('The biological origin field is only used for Wild strain!')
                    ->atPath('biologicalOrigin')
                    ->addViolation();
            }

            if (null !== $this->source) {
                $context->buildViolation('The source field is only used for Wild strain!')
                    ->atPath('source')
                    ->addViolation();
            }

            if (null !== $this->latitude) {
                $context->buildViolation('The latitude field is only used for Wild strain!')
                    ->atPath('latitude')
                    ->addViolation();
            }

            if (null !== $this->longitude) {
                $context->buildViolation('The longitude field is only used for Wild strain!')
                    ->atPath('longitude')
                    ->addViolation();
            }

            if (null !== $this->address) {
                $context->buildViolation('The address field is only used for Wild strain!')
                    ->atPath('address')
                    ->addViolation();
            }

            if (null !== $this->country) {
                $context->buildViolation('The country field is only used for Wild strain!')
                    ->atPath('country')
                    ->addViolation();
            }
        } else {
            if (null !== $this->description) {
                $context->buildViolation('The description field is only used for GMO strain!')
                    ->atPath('description')
                    ->addViolation();
            }

            if (null !== $this->genotype) {
                $context->buildViolation('The genotype field is only used for GMO strain!')
                    ->atPath('genotype')
                    ->addViolation();
            }

            if (!$this->parents->isEmpty()) {
                $context->buildViolation('The parents field is only used for GMO strain!')
                    ->atPath('genotype')
                    ->addViolation();
            }

            if (!$this->strainPlasmids->isEmpty()) {
                $context->buildViolation('The plasmids field is only used for GMO strain!')
                    ->atPath('genotype')
                    ->addViolation();
            }
        }
    }
}
