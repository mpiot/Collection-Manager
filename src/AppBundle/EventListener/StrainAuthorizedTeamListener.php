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

        $teams = [];

        foreach($strain->getTubes() as $tube)
        {
            foreach($tube->getBox()->getProject()->getTeams() as $team) {
                if (!in_array($team->getId(), $teams)) {
                    $teams[] = $team->getId();
                }
            }
        }

        $document->set('authorizedTeams', $teams);
    }

    public static function getSubscribedEvents()
    {
        return array(
            TransformEvent::POST_TRANSFORM => 'addStrainAuthorizedTeam',
        );
    }
}
