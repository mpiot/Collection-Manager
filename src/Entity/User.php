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
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("email")
 */
class User implements UserInterface, \Serializable
{
    const ROLE_DEFAULT = 'ROLE_USER';
    const NUM_ITEMS = 10;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     * @Assert\Regex(
     *     pattern="/.+@inra.fr/",
     *     message="You must use an @inra.fr email."
     * )
     */
    private $email;

    /**
     * @Assert\Length(max=4096)
     * @Assert\Regex("/[a-z]/", message="Your password must contain at least one lowercase letter.")
     * @Assert\Regex("/[A-Z]/", message="Your password must contain at least one uppercase letter.")
     * @Assert\Regex("/[\d]/", message="Your password must contain at least one number.")
     * @Assert\Length(min=8, max=4096)
     */
    private $plainPassword;

    /**
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(name="roles", type="array")
     */
    private $roles;

    /**
     * @ORM\Column(name="enabled", type="boolean", nullable=true)
     */
    private $enabled;

    /**
     * @ORM\Column(name="confirmation_token", type="string", nullable=true)
     */
    private $confirmationToken;

    /**
     * @ORM\Column(name="first_name", type="string", length=255)
     * @Assert\NotBlank(message="Please enter your first name.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     minMessage="The name is too short.",
     *     maxMessage="The name is too long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    private $firstName;

    /**
     * @ORM\Column(name="last_name", type="string", length=255)
     * @Assert\NotBlank(message="Please enter your last name.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     minMessage="The name is too short.",
     *     maxMessage="The name is too long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    private $lastName;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Group", mappedBy="administrators")
     */
    private $administeredGroups;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Group", mappedBy="members")
     */
    private $groups;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Group")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $favoriteGroup;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Strain", mappedBy="createdBy")
     */
    private $strains;

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

    public function __construct()
    {
        $this->roles = [];
        $this->enabled = false;
        $this->groups = new ArrayCollection();
        $this->administeredGroups = new ArrayCollection();
        $this->strains = new ArrayCollection();
    }

    /**
     * Get ID.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set email.
     *
     * @param $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Get username.
     */
    public function getUsername(): string
    {
        return $this->email;
    }

    /**
     * Set plain password.
     *
     * @param $password
     */
    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    /**
     * Get plain password.
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Set password.
     *
     * @param $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password.
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Get salt.
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Set roles.
     *
     * @param $array
     *
     * @return $this
     */
    public function setRoles($array)
    {
        $this->roles = $array;

        return $this;
    }

    /**
     * Add role.
     *
     * @param $role
     *
     * @return $this
     */
    public function addRole($role)
    {
        $role = mb_strtoupper($role);
        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }

        if (!\in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * Remove role.
     *
     * @param $role
     *
     * @return $this
     */
    public function removeRole($role)
    {
        if (false !== $key = array_search($role, $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * Get roles.
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
    }

    /**
     * Set enabled.
     *
     * @param bool $enabled
     */
    public function setEnabled($enabled): self
    {
        $this->enabled = $enabled;
        $this->confirmationToken = null;

        return $this;
    }

    /**
     * Set confirmation token.
     *
     * @param string $confirmationToken
     */
    public function setConfirmationToken($confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    /**
     * Get confirmation token.
     */
    public function getConfirmationToken(): string
    {
        return $this->confirmationToken;
    }

    /**
     * Erase credentials.
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    /**
     * Is enabled ?
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Serialize.
     */
    public function serialize(): string
    {
        return serialize([
            $this->id,
            $this->email,
            $this->password,
            $this->enabled,
        ]);
    }

    /**
     * Unserialize.
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->email,
            $this->password,
            $this->enabled
        ) = unserialize($serialized);
    }

    /**
     * Set firstName.
     *
     * @param $firstName
     *
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName.
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName.
     *
     *
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName.
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Get fullName.
     */
    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }

    /**
     * Add administered group.
     */
    public function addAdministeredGroup(Group $group)
    {
        $this->administeredGroups->add($group);
    }

    public function getAdministeredGroups(): ArrayCollection
    {
        return $this->administeredGroups;
    }

    /**
     * Get administered groups Ids.
     */
    public function getAdministeredGroupsId(): array
    {
        $administeredGroupsId = [];

        foreach ($this->administeredGroups as $administeredGroup) {
            $administeredGroupsId[] = $administeredGroup->getId();
        }

        return $administeredGroupsId;
    }

    /**
     * Is a group administrator ?
     */
    public function isGroupAdministrator(): bool
    {
        return !$this->administeredGroups->isEmpty();
    }

    /**
     * Add group.
     */
    public function addGroup(Group $group)
    {
        $this->groups->add($group);
    }

    /**
     * Get groups.
     */
    public function getGroups(): ArrayCollection
    {
        return $this->groups;
    }

    /**
     * Get groups Ids.
     */
    public function getGroupsId(): array
    {
        $groupsId = [];

        foreach ($this->groups as $group) {
            $groupsId[] = $group->getId();
        }

        return $groupsId;
    }

    /**
     * Is in a group ?
     */
    public function isInGroup(): bool
    {
        return !$this->groups->isEmpty();
    }

    /**
     * Is in this groups ?
     */
    public function hasGroup(Group $group): bool
    {
        return $this->groups->contains($group);
    }

    /**
     * Set favorite group.
     *
     *
     * @return $this
     */
    public function setFavoriteGroup(Group $group)
    {
        $this->favoriteGroup = $group;

        return $this;
    }

    /**
     * Get favorite group.
     */
    public function getFavoriteGroup(): Group
    {
        // If the user have no set a favorite Group, the first match group is the favorite
        if (null === $this->favoriteGroup) {
            return $this->groups->first();
        }

        return $this->favoriteGroup;
    }

    /**
     * Is favorite group ?
     */
    public function isFavoriteGroup(Group $group): bool
    {
        return $group === $this->getFavoriteGroup();
    }

    /**
     * Get strains.
     */
    public function getStrains(): ArrayCollection
    {
        return $this->strains;
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
}
