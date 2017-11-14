<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Project;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProjectSubscriber implements EventSubscriber
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'onFlush',
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        // If the entity is not a Project, return
        if (!$entity instanceof Project) {
            return;
        }

        // If the token is null (DataFixtures, return)
        if (null === $this->tokenStorage->getToken()) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();

        // If the user is administrator of the selected Team, automatically validate the project
        if ($user->isAdministratorOf($entity->getTeam())) {
            $entity->setValid(true);
        }
    }

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
