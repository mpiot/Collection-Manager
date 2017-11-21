<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * File.
 *
 * @ORM\Entity
 * @ORM\Table(name="file")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"file" = "File", "genBankFile" = "GenBankFile", "documentFile" = "DocumentFile"})
 * @ORM\HasLifecycleCallbacks
 */
class File
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
     * The path of the file on the server.
     *
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255, unique=true)
     */
    private $path;

    /**
     * The path of the file on the server (before import).
     *
     * @var string
     */
    protected $fileSystemPath;

    /**
     * A temporaty path, before deletion.
     *
     * @var string
     */
    private $tempPath;

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
     * Set path.
     *
     * @param string $path
     *
     * @return File
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set fileSystemPath.
     *
     * @param string $fileSystemPath
     *
     * @return File
     */
    public function setFileSystemPath($fileSystemPath)
    {
        $this->fileSystemPath = $fileSystemPath;

        // Check if the entity have already an attached file
        if (null !== $this->path) {
            // Copy the path in tempPath
            $this->tempPath = $this->getAbsolutePath();

            // Change the path to null
            $this->path = null;
        }

        return $this;
    }

    /**
     * Get fileSystemPath.
     *
     * @return string
     */
    public function getFileSystemPath()
    {
        return $this->fileSystemPath;
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

    /**
     * Get XSendfile upload dir.
     *
     * @return string
     */
    public function getXSendfileUploadDir()
    {
        return realpath(__DIR__.'/../../../files');
    }

    /**
     * Get upload dir.
     *
     * @return string
     */
    protected function getUploadDir()
    {
        // on se débarrasse de « __DIR__ » afin de ne pas avoir de problème lorsqu'on récupère le fichier
        return 'files';
    }

    /**
     * Get upload root dir.
     *
     * @return string
     */
    protected function getUploadRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __DIR__.'/../../../'.$this->getUploadDir();
    }

    /**
     * Get absolute path.
     *
     * @return null|string
     */
    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
    }

    /**
     * Get webPath.
     */
    public function getWebPath()
    {
        return $this->getUploadDir().'/'.$this->getPath();
    }

    /**
     * Before persist or update.
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null === $this->fileSystemPath) {
            return;
        }

        $this->path = uniqid(rand(), true).'.'.$this->fileSystemPath->getClientOriginalExtension();
    }

    /**
     * After persist or update.
     *
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function postUpload()
    {
        if (null === $this->fileSystemPath) {
            return;
        }

        if (null !== $this->tempPath) {
            if (file_exists($this->tempPath)) {
                unlink($this->tempPath);
            }
        }

        $this->fileSystemPath->move($this->getUploadRootDir(), $this->path);
    }

    /**
     * Before remove.
     *
     * @ORM\PreRemove()
     */
    public function preRemoveUpload()
    {
        $this->tempPath = $this->getAbsolutePath();
    }

    /**
     * After remove.
     *
     * @ORM\PostRemove()
     */
    public function postRemoveUpload()
    {
        if (file_exists($this->tempPath)) {
            unlink($this->tempPath);
        }
    }
}
