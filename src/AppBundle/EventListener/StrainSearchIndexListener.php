<?php

namespace AppBundle\EventListener;

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

        $document->set('type', $strain->getType()->getId());
    }

    public static function getSubscribedEvents()
    {
        return array(
            TransformEvent::POST_TRANSFORM => 'addCustomProperties',
        );
    }
}
