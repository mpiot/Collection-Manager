<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Species;
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

        // Get the genus
        $genus = $entity->getGenus();

        // If genus doen't have children, delete the genus
        if ($genus->getSpecies()->isEmpty()) {
            $em = $args->getEntityManager();
            $em->remove($genus);
        }
    }
}
