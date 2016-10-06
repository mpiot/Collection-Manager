<?php

namespace AppBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use AppBundle\Entity\GmoStrain;
use AppBundle\Entity\WildStrain;

class StrainListener
{
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // If the entity is not a GmoStrain and not a WildStrain, return
        if (!$entity instanceof GmoStrain && !$entity instanceof WildStrain) {
            return;
        }

        // Else, we have a GmoStrain ot a WildStrain
        $em = $args->getEntityManager();

        // Persist tubes
        foreach ($entity->getTubes() as $tube) {
            $em->persist($tube);
        }

        // After tubes persisted, generate AutoName
        $entity->generateAutoName();
    }
}
