<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Strain;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\ElasticaBundle\Event\TransformEvent;

class StrainSearchIndexListener implements EventSubscriberInterface
{
    public function addCustomProperties(TransformEvent $event)
    {
        $this->addStrainProjects($event);
    }

    protected function addStrainProjects(TransformEvent $event)
    {
        $document = $event->getDocument();
        $strain = $event->getObject();

        if (!$strain instanceof Strain) {
            return;
        }

        $tubes = $strain->getTubes();
        $projects = [];

        foreach ($tubes as $tube) {
            if (!in_array($projectId = $tube->getBox()->getProject()->getId(), $projects)) {
                $projects[] = $projectId;
            }
        }

        $document->set('project_id', $projects);
    }

    public static function getSubscribedEvents()
    {
        return [
            TransformEvent::POST_TRANSFORM => 'addCustomProperties',
        ];
    }
}
