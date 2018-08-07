<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("email")
 */
class User implements AdvancedUserInterface, \Serializable
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
        $this->isActive = false;
        $this->groups = new ArrayCollection();
        $this->administeredGroups = new ArrayCollection();
        $this->strains = new ArrayCollection();
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
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
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername()
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
     *
     * @return mixed
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
     *
     * @return string
     */
    public function getPassword()
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

        if (!in_array($role, $this->roles, true)) {
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
     *
     * @return array
     */
    public function getRoles()
    {
        $roles = $this->roles;
        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
    }

    /**
     * Set enabled.
     *
     * @param bool $enabled
     *
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        $this->confirmationToken = null;

        return $this;
    }

    /**
     * Set confirmation token.
     *
     * @param string $confirmationToken
     *
     * @return User
     */
    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    /**
     * Get confirmation token.
     *
     * @return string
     */
    public function getConfirmationToken()
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
     * Is account non expired ?
     *
     * @return bool
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * Is account non locked ?
     *
     * @return bool
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * Is credential non expired ?
     *
     * @return bool
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Is enabled ?
     *
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Serialize.
     *
     * @return string
     */
    public function serialize()
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
     *
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName.
     *
     * @param $firstName
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
     *
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Get fullName.
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->firstName.' '.$this->lastName;
    }

    /**
     * Add administered group.
     *
     * @param Group $group
     */
    public function addAdministeredGroup(Group $group)
    {
        $this->administeredGroups->add($group);
    }

    /**
     * @return ArrayCollection
     */
    public function getAdministeredGroups()
    {
        return $this->administeredGroups;
    }

    /**
     * Get administered groups Ids.
     *
     * @return array
     */
    public function getAdministeredGroupsId()
    {
        $administeredGroupsId = [];

        foreach ($this->administeredGroups as $administeredGroup) {
            $administeredGroupsId[] = $administeredGroup->getId();
        }

        return $administeredGroupsId;
    }

    /**
     * Is a group administrator ?
     *
     * @return bool
     */
    public function isGroupAdministrator()
    {
        return !$this->administeredGroups->isEmpty();
    }

    /**
     * Add group.
     *
     * @param Group $group
     */
    public function addGroup(Group $group)
    {
        $this->groups->add($group);
    }

    /**
     * Get groups.
     *
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Get groups Ids.
     *
     * @return array
     */
    public function getGroupsId()
    {
        $groupsId = [];

        foreach ($this->groups as $group) {
            $groupsId[] = $group->getId();
        }

        return $groupsId;
    }

    /**
     * Is in a group ?
     *
     * @return bool
     */
    public function isInGroup()
    {
        return !$this->groups->isEmpty();
    }

    /**
     * Is in this groups ?
     *
     * @param Group $group
     *
     * @return bool
     */
    public function hasGroup(Group $group)
    {
        return $this->groups->contains($group);
    }

    /**
     * Set favorite group.
     *
     * @param Group $group
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
     *
     * @return Group
     */
    public function getFavoriteGroup()
    {
        // If the user have no set a favorite Group, the first match group is the favorite
        if (null === $this->favoriteGroup) {
            return $this->groups->first();
        }

        return $this->favoriteGroup;
    }

    /**
     * Is favorite group ?
     *
     * @param Group $group
     *
     * @return bool
     */
    public function isFavoriteGroup(Group $group)
    {
        return $group === $this->getFavoriteGroup();
    }

    /**
     * Get strains.
     *
     * @return ArrayCollection
     */
    public function getStrains()
    {
        return $this->strains;
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
}
