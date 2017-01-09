<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\GmoStrain;
use AppBundle\Entity\WildStrain;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\ElasticaBundle\Event\TransformEvent;

class StrainSearchIndexListener implements EventSubscriberInterface
{
    public function addCustomProperties(TransformEvent $event)
    {
        $this->addStrainProjects($event);
        $this->addStrainType($event);
    }

    protected function addStrainProjects(TransformEvent $event)
    {
        $document = $event->getDocument();
        $strain = $event->getObject();

        if (!$strain instanceof GmoStrain && !$strain instanceof WildStrain) {
            return;
        }

        $tubes = $strain->getTubes();
        $projects = [];

        foreach ($tubes as $tube) {
            if (!in_array($projectId = $tube->getBox()->getProject()->getId(), $projects)) {
                $projects[] = $projectId;
            }
        }

        $document->set('projects', $projects);
    }

    protected function addStrainType(TransformEvent $event)
    {
        $document = $event->getDocument();
        $strain = $event->getObject();

        if (!$strain instanceof GmoStrain && !$strain instanceof WildStrain) {
            return;
        }

        $document->set('type', $strain->getType()->getId());
    }

    public static function getSubscribedEvents()
    {
        return [
            TransformEvent::POST_TRANSFORM => 'addCustomProperties',
        ];
    }
}
