<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Group;
use Doctrine\ORM\Event\OnFlushEventArgs;

class GroupListener
{
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        // When user create a new Group
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if (!$entity instanceof Group) {
                return;
            }

            $group = $this->addAsMemberGroupAdministrators($entity);

            $metaData = $em->getClassMetadata('AppBundle\Entity\Group');
            $uow->computeChangeSet($metaData, $group);
        }

        // When user edit an existant Group
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!$entity instanceof Group) {
                return;
            }

            $group = $this->addAsMemberGroupAdministrators($entity);

            $metaData = $em->getClassMetadata('AppBundle\Entity\Group');
            $uow->computeChangeSet($metaData, $group);
        }
    }

    private function addAsMemberGroupAdministrators(Group $group)
    {
        foreach ($group->getAdministrators()->toArray() as $administrator) {
            if (!$group->getMembers()->contains($administrator)) {
                $group->addMember($administrator);
            }
        }

        return $group;
    }
}
