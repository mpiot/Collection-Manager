<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Project;
use Doctrine\ORM\Event\OnFlushEventArgs;

class ProjectListener
{
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        // When user create a new Project
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if (!$entity instanceof Project) {
                return;
            }

            $project = $this->addAsMemberProjectAdministrators($entity);

            $metaData = $em->getClassMetadata('AppBundle\Entity\Project');
            $uow->computeChangeSet($metaData, $project);
        }

        // When user edit an existant Team
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!$entity instanceof Project) {
                return;
            }

            $project = $this->addAsMemberProjectAdministrators($entity);

            $metaData = $em->getClassMetadata('AppBundle\Entity\Project');
            $uow->computeChangeSet($metaData, $project);
        }
    }

    private function addAsMemberProjectAdministrators(Project $project)
    {
        foreach ($project->getAdministrators()->toArray() as $administrator) {
            if (!$project->getMembers()->contains($administrator)) {
                $project->addMember($administrator);
            }
        }

        return $project;
    }
}
