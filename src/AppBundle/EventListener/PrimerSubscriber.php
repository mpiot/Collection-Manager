<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Primer;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class PrimerSubscriber implements EventSubscriber
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

        // If the entity is not a Primer return
        if (!$entity instanceof Primer) {
            return;
        }

        $primerNumber = $entity->getTeam()->getLastPrimerNumber() + 1;
        $autoName = 'primer'.str_pad($primerNumber, 4, '0', STR_PAD_LEFT);

        // Set autoName
        $entity->setAutoName($autoName);
        $entity->getTeam()->setLastPrimerNumber($primerNumber);

        $entity->setAuthor($this->tokenStorage->getToken()->getUser());
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // If the entity is not a Primer return
        if (!$entity instanceof Primer) {
            return;
        }

        $entity->setLastEditor($this->tokenStorage->getToken()->getUser());
        $entity->setLastEdit(new \DateTime());
    }
}
