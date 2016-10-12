<?php

namespace AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\ElasticaBundle\Event\TransformEvent;

class StrainAuthorizedTeamListener implements EventSubscriberInterface
{
    public function addStrainAuthorizedTeam(TransformEvent $event)
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

    public static function getSubscribedEvents()
    {
        return array(
            TransformEvent::POST_TRANSFORM => 'addStrainAuthorizedTeam',
        );
    }
}
