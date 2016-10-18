<?php

namespace AppBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use AppBundle\Entity\GmoStrain;
use AppBundle\Entity\WildStrain;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class StrainListener
{
    private $tokenStorage;

    public function __construct(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // If the entity is not a GmoStrain and not a WildStrain, return
        if (!$entity instanceof GmoStrain && !$entity instanceof WildStrain) {
            return;
        }

        $em = $args->getEntityManager();

        // Persist tubes
        foreach ($entity->getTubes() as $tube) {
            $em->persist($tube);
        }

        // After tubes persisted, generate AutoName
        $entity->generateAutoName();

        // Define the author
        $entity->setAuthor($this->tokenStorage->getToken()->getUser());
    }
}
