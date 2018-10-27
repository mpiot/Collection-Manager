<?php

namespace App\EventListener;

use App\Entity\Species;
use Doctrine\ORM\Event\LifecycleEventArgs;

class SpeciesListener
{
    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // If the entity is not a Species return
        if (!$entity instanceof Species) {
            return;
        }

        $genus = $entity->getGenus();

        $em = $args->getEntityManager();
        $speciesList = $em->getRepository('App:Species')->findByGenus($genus->getId());

        if (0 === \count($speciesList)) {
            $em->remove($genus);
        }
    }
}
