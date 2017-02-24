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
        }

        $plasmidNumber = $entity->getTeam()->getLastPlasmidNumber() + 1;
        $autoName = 'p'.str_pad($plasmidNumber, 4, '0', STR_PAD_LEFT);

        // Set autoName
        $entity->setAutoName($autoName);
        $entity->getTeam()->setLastPlasmidNumber($plasmidNumber);

        $entity->setAuthor($this->tokenStorage->getToken()->getUser());
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // If the entity is not a Plasmid return
        if (!$entity instanceof Plasmid) {
            return;
        }

        $entity->setLastEditor($this->tokenStorage->getToken()->getUser());
        $entity->setLastEdit(new \DateTime());
    }
}
