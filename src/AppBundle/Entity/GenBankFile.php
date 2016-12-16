<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * GenBankFile.
 *
 * @ORM\Table(name="gen_bank_file")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GenBankFileRepository")
 */
class GenBankFile extends File
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
     * The concerned plasmid.
     *
     * @var Plasmid
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Plasmid", inversedBy="genBankFile")
     */
    private $plasmid;

    /**
     * The path of the file on the server (before import).
     *
     * @var string
     *
     * @Assert\File(mimeTypes = {"text/plain"})
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
     * Set Plasmid.
     *
     * @param Plasmid $plasmid
     *
     * @return $this
     */
    public function setPlasmid(Plasmid $plasmid)
    {
        $this->plasmid = $plasmid;

        return $this;
    }

    /**
     * Get plasmid.
     *
     * @return Plasmid
     */
    public function getPlasmid()
    {
        return $this->plasmid;
    }

    /**
     * Get the upload root directory.
     *
     * @return string
     */
    protected function getUploadRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __DIR__.'/../../../web/'.$this->getUploadDir();
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
        return 'uploads/genBankFiles';
    }
}
