<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Team;
use Doctrine\ORM\Event\OnFlushEventArgs;

class TeamListener
{
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        // When user create a new Team
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if (!$entity instanceof Team) {
                return;
            }

            $team = $this->addAsMemberTeamAdministrators($entity);

            $metaData = $em->getClassMetadata('AppBundle\Entity\Team');
            $uow->computeChangeSet($metaData, $team);
        }

        // When user edit an existant Team
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!$entity instanceof Team) {
                return;
            }

            $team = $this->addAsMemberTeamAdministrators($entity);

            $metaData = $em->getClassMetadata('AppBundle\Entity\Team');
            $uow->computeChangeSet($metaData, $team);
        }
    }

    private function addAsMemberTeamAdministrators(Team $team)
    {
        foreach ($team->getAdministrators()->toArray() as $administrator) {
            if (!$team->getMembers()->contains($administrator)) {
                $team->addMember($administrator);
            }
        }

        return $team;
    }
}
