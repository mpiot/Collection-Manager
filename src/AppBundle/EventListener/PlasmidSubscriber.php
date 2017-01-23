<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Plasmid;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class PlasmidSubscriber implements EventSubscriber
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

        // If the entity is not a Plasmid return
        if (!$entity instanceof Plasmid) {
            return;
        } else {
            $plasmid = $entity;
        }

        $plasmidNumber = $plasmid->getTeam()->getLastPlasmidNumber() + 1;

        // Determine how many 0 put before the number
        $nbDigit = 4;
        $numberOf0 = $nbDigit - floor(log10($plasmidNumber) + 1);

        if ($numberOf0 < 0) {
            $numberOf0 = 0;
        }

        $autoName = 'p'.str_repeat('0', $numberOf0).$plasmidNumber;

        // Set autoName
        $plasmid->setAutoName($autoName);
        $plasmid->getTeam()->setLastPlasmidNumber($plasmidNumber);

        $plasmid->setAuthor($this->tokenStorage->getToken()->getUser());
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // If the entity is not a Plasmid return
        if (!$entity instanceof Plasmid) {
            return;
        } else {
            $plasmid = $entity;
        }

        $plasmid->setLastEditor($this->tokenStorage->getToken()->getUser());
        $plasmid->setLastEdit(new \DateTime());
    }
}
