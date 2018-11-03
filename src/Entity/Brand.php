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
 * Brand.
 *
 * @ORM\Table(name="brand")
 * @ORM\Entity(repositoryClass="App\Repository\BrandRepository")
 * @UniqueEntity("name")
 */
class Brand
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
     * @ORM\OneToMany(targetEntity="App\Entity\Product", mappedBy="brand")
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
        $this->products = new ArrayCollection();
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
     * Get products.
     */
    public function getProducts(): ArrayCollection
    {
        return $this->products;
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
