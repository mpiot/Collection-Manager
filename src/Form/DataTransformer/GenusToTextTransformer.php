<?php

namespace App\Form\DataTransformer;

use App\Entity\Genus;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;

class GenusToTextTransformer implements DataTransformerInterface
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Transforms an object (genus) to a string (text).
     *
     * @param Genus $genus
     *
     * @return string
     */
    public function transform($genus)
    {
        if (null === $genus) {
            return '';
        }

        return $genus->getName();
    }

    public function reverseTransform($genusName)
    {
        if (!$genusName) {
            return;
        }

        $genus = $this->manager
            ->getRepository('App:Genus')
            ->findOneByName($genusName);

        if (null === $genus) {
            // Create the genus entity
            $genus = new Genus();
            $genus->setName($genusName);

            $this->manager->persist($genus);
            $this->manager->flush();
        }

        return $genus;
    }
}
