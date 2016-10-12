<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Team;
use Doctrine\ORM\Event\OnFlushEventArgs;

class TeamListener
{
    public function onFlush(OnFlushEventArgs $args) {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        // When user create a new Team
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if (!$entity instanceof Team) {
                return;
            }

            $team = $this->addAdministratorAndModeratorAsUser($entity);

            $metaData = $em->getClassMetadata('AppBundle\Entity\Team');
            $uow->computeChangeSet($metaData, $team);
        }

        // When user edit an existant Team
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            dump($entity);

            if (!$entity instanceof Team) {
                return;
            }

            dump($entity->getAdministrators()->toArray());
            dump($entity->getModerators()->toArray());
            dump($entity->getMembers()->toArray());

            $team = $this->addAdministratorAndModeratorAsUser($entity);

            dump($team->getAdministrators()->toArray());
            dump($team->getModerators()->toArray());
            dump($team->getMembers()->toArray());

            $metaData = $em->getClassMetadata('AppBundle\Entity\Team');
            $uow->computeChangeSet($metaData, $team);
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            dump($entity);
        }
    }

    private function addAdministratorAndModeratorAsUser(Team $team)
    {
        foreach ($team->getAdministrators()->toArray() as $administrator)
        {
            if (!$team->getMembers()->contains($administrator)) {
                $team->addMember($administrator);
            }
        }

        foreach ($team->getModerators()->toArray() as $moderator)
        {
            if (!$team->getMembers()->contains($moderator)) {
                $team->addMember($moderator);
            }
        }

        return $team;
    }
}
