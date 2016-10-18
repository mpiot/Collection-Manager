<?php

namespace AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\ElasticaBundle\Event\TransformEvent;

class StrainSearchIndexListener implements EventSubscriberInterface
{
    public function addCustomProperties(TransformEvent $event)
    {
        $this->addStrainAuthorizedTeam($event);
        $this->addStrainProjects($event);
    }

    protected function addStrainAuthorizedTeam(TransformEvent $event)
    {
        $document = $event->getDocument();
        $strain = $event->getObject();

        $teams = $strain->getAuthorizedTeams();
        $teamsIds = [];

        foreach ($teams as $team) {
            $teamsIds[] = $team->getId();
        }

        $document->set('authorizedTeams', $teamsIds);
    }

    protected function addStrainProjects(TransformEvent $event)
    {
        $document = $event->getDocument();
        $strain = $event->getObject();

        $tubes = $strain->getTubes();
        $projects = [];

        foreach ($tubes as $tube) {
            if (!in_array($projectId = $tube->getBox()->getProject()->getId(), $projects)) {
                $projects[] = $projectId;
            }
        }

        $document->set('projects', $projects);
    }

    public static function getSubscribedEvents()
    {
        return array(
            TransformEvent::POST_TRANSFORM => 'addCustomProperties',
        );
    }
}
