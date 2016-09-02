<?php

namespace AppBundle\Form\EventListener;

use AppBundle\Entity\Box;
use AppBundle\Entity\Project;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormInterface;

class TubeDynamicFieldSubscriber implements EventSubscriberInterface
{
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmitData',
        );
    }

    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $tube = $event->getData();

        if (null !== $tube) {
            $project = $tube->getBox()->getProject();
            $box = $tube->getBox();
            $cell = $tube->getCell();
        } else {
            $project = null;
            $box = null;
            $cell = null;
        }

        // Add Project field as its static
        $form->add('project', EntityType::class, array(
            'class' => 'AppBundle\Entity\Project',
            'choice_label' => 'name',
            'placeholder' => '-- select a project --',
            'mapped' => false,
            'data' => $project,
        ));
        $this->addBoxField($form, $project);
        $this->addCellField($form, $box, $cell);
    }

    public function preSubmitData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData(); // Data is an array

        // Add property field if parent entity data is available
        $project = isset($data['project']) ? $data['project'] : null;
        $box = isset($data['box']) ? $data['box'] : null;

        // Call method to add child fields
        $this->addBoxField($form, $project);
        $this->addCellField($form, $box);
    }

    private function addBoxField(FormInterface $form, $project = null)
    {
        $form->add('box', EntityType::class, array(
            'class' => 'AppBundle\Entity\Box',
            'choice_label' => 'name',
            'placeholder' => '-- select a box --',
            'query_builder' => function(EntityRepository $er) use ($project) {
                return $er->createQueryBuilder('b')
                        ->leftJoin('b.project', 'p')
                        ->where('p.id = :project')
                        ->setParameter('project', $project);
            }
        ));
    }

    private function addCellField(FormInterface $form, $box = null, $cell = null)
    {
        // If $box contains the ID, Serialize the box
        if (null !== $box) {
            $box = $this->em->find('AppBundle:Box', $box);
        }

        // If $box is a Box
        if (null !== $box && is_object($box) && is_a($box, Box::class)) {
            $cells = $box->getEmptyCells($cell);
        } else {
            $cells = [];
        }

        $form->add('cell', ChoiceType::class, array(
            'placeholder' => '-- select a cell --',
            'choices' => $cells,
            'data' => $cell,
        ));
    }
}
