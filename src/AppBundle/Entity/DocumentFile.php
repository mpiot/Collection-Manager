<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DocumentFile.
 *
 * @ORM\Table(name="document_file")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DocumentFileRepository")
 */
class DocumentFile extends File
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
     * The path of the file on the server (before import).
     *
     * @var string
     *
     * @Assert\File(mimeTypes = {"application/pdf"})
     */
    protected $fileSystemPath;

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
     * Get the upload root directory.
     *
     * @return string
     */
    protected function getUploadRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __DIR__.'/../../../'.$this->getUploadDir();
    }

    /**
     * Get the upload directory.
     *
     * @return string
     */
    protected function getUploadDir()
    {
        // on se débarrasse de « __DIR__ » afin de ne pas avoir de problème lorsqu'on affiche
        // le document/image dans la vue.
        return 'files/documents';
    }
}
