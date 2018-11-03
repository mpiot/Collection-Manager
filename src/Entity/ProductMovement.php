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
 * Product.
 *
 * @ORM\Table(name="product_movement")
 * @ORM\Entity(repositoryClass="App\Repository\ProductMovementRepository")
 */
class ProductMovement
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", inversedBy="movements")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @var int
     *
     * @ORM\Column(name="movement", type="integer")
     * @Assert\NotBlank()
     */
    private $movement;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text")
     * @Assert\NotBlank()
     */
    private $comment;

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
     * @var string
     *
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $createdBy;

    /**
     * @var string
     *
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $updatedBy;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Get id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set movement.
     *
     * @param int $movement
     */
    public function setMovement($movement): self
    {
        $this->movement = $movement;

        return $this;
    }

    /**
     * Get movement.
     */
    public function getMovement(): int
    {
        return $this->movement;
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
     * Set created.
     *
     * @param \DateTime $created
     */
    public function setCreated($created): self
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     */
    public function setUpdated($updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     */
    public function getUpdated(): \DateTime
    {
        return $this->updated;
    }

    /**
     * Set createdBy.
     *
     * @param string $createdBy
     */
    public function setCreatedBy($createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy.
     */
    public function getCreatedBy(): string
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
     * Set updatedBy.
     *
     * @param string $updatedBy
     */
    public function setUpdatedBy($updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy.
     */
    public function getUpdatedBy(): string
    {
        return $this->updatedBy;
    }

    /**
     * Set product.
     */
    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product.
     */
    public function getProduct(): \App\Entity\Product
    {
        return $this->product;
    }
}
