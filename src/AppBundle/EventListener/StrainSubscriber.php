<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Strain;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class StrainSubscriber implements EventSubscriber
{
    private $tokenStorage;

    public function __construct(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'preUpdate',
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // If the entity is not a GmoStrain and not a WildStrain, return
        if (!$entity instanceof Strain) {
            return;
        }

        $strainProject = $entity->getTubes()->first()->getProject();
        $strainNumber = $strainProject->getLastStrainNumber() + 1;
        $strainPrefix = $strainProject->getPrefix();

        $autoName = $strainPrefix.'_'.str_pad($strainNumber, 4, '0', STR_PAD_LEFT);

        // Set autoName
        $entity->setAutoName($autoName);
        $strainProject->setLastStrainNumber($strainNumber);

        // Define the author
        $entity->setAuthor($this->tokenStorage->getToken()->getUser());

        // Convert synonym species to mainSpecies
        $this->synonym2MainSpecies($entity);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // If the entity is not a GmoStrain and not a WildStrain, return
        if (!$entity instanceof Strain) {
            return;
        }

        // Convert synonym species to mainSpecies
        $this->synonym2MainSpecies($entity);
    }

    private function synonym2MainSpecies(Strain $strain)
    {
        $species = $strain->getSpecies();

        if (!$species->isMainSpecies()) {
            $strain->setSpecies($species->getMainSpecies());
        }
    }
}
