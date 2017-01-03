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

        if (1 !== $plasmidNumber) {
            // Determine how many 0 put before the number
            $nbDigit = 4;
            $numberOf0 = $nbDigit - ceil(log10($plasmidNumber));
            $autoName = 'p'.str_repeat('0', $numberOf0).$plasmidNumber;
        } else {
            $autoName = 'p0001';
        }

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
