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

/**
 * StrainPlasmid.
 *
 * @ORM\Table(name="strain_plasmid")
 * @ORM\Entity(repositoryClass="App\Repository\StrainPlasmidRepository")
 */
class StrainPlasmid
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Strain
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Strain", inversedBy="strainPlasmids")
     * @ORM\JoinColumn(nullable=false)
     */
    private $strain;

    /**
     * @var Plasmid
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Plasmid", inversedBy="strainPlasmids")
     * @ORM\JoinColumn(nullable=false)
     */
    private $plasmid;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=255)
     */
    private $state;

    public function __toString()
    {
        return $this->getPlasmid().' ('.$this->state.')';
    }

    /**
     * Get id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set strain.
     */
    public function setStrain(Strain $strain): self
    {
        $this->strain = $strain;

        return $this;
    }

    /**
     * Get strain.
     */
    public function getStrain(): Strain
    {
        return $this->strain;
    }

    /**
     * Set plasmid.
     */
    public function setPlasmid(Plasmid $plasmid): self
    {
        $this->plasmid = $plasmid;

        return $this;
    }

    /**
     * Get plasmid.
     */
    public function getPlasmid(): Plasmid
    {
        return $this->plasmid;
    }

    /**
     * Set state.
     *
     * @param string $state
     */
    public function setState($state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state.
     */
    public function getState(): string
    {
        return $this->state;
    }
}
