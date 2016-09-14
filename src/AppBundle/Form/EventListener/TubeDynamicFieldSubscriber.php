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
    private $box;
    private $cell;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
        );
    }

    protected function addElement(FormInterface $form, Project $project = null, Box $box = null, $previousCell = null)
    {
        $form->add('project', EntityType::class, array(
            'class' => 'AppBundle\Entity\Project',
            'choice_label' => 'name',
            'placeholder' => '-- select a project --',
            'mapped' => false,
            'data' => $project,
        ));

        $form->add('box', EntityType::class, array(
            'class' => 'AppBundle\Entity\Box',
            'choice_label' => 'name',
            'placeholder' => '-- select a box --',
            'query_builder' => function(EntityRepository $er) use ($project) {
                return $er->createQueryBuilder('b')
                        ->leftJoin('b.project', 'p')
                        ->where('p = :project')
                        ->setParameter('project', $project);
            },
            'data' => $box,
        ));

        $cells = null !== $box ? $box->getEmptyCells($previousCell) : null;

        $form->add('cell', ChoiceType::class, array(
            'placeholder' => '-- select a cell --',
            'choices' => $cells,
            'data' => $previousCell,
        ));
    }

    public function onPreSetData(FormEvent $event)
    {
        // Store form and data
        $form = $event->getForm();
        $tube = $event->getData();

        // If it's a new tube, no box and no cell exists
        $box = null !== $tube && null !== $tube->getBox() ? $tube->getBox() : null;
        $project = null !== $box ? $box->getProject() : null;
        $cell = null !== $tube && null !== $tube->getCell() ? $tube->getCell() : null;

        // Keep default value
        $this->box = $box;
        $this->cell = $cell;

        // Add the form
        $this->addElement($form, $project, $box, $cell);
    }

    public function onPreSubmit(FormEvent $event)
    {
        // Store form and data
        $form = $event->getForm();
        $data = $event->getData();

        // Verify data send by user or ajax, and store it
        if (isset($data['project'])) {
            $project = $this->em->getRepository('AppBundle:Project')->findOneById($data['project']);
        } else {
            $project = null;
        }

        if (isset($data['box'])) {
            $box = $this->em->getRepository('AppBundle:Box')->findOneById($data['box']);
        } else {
            $box = null;
        }

        if (isset($data['cell'])) {
            $cell = (int) $data['cell'];
        } else {
            $cell = null;
        }

        // If it's an ajax call, and it's a project request
        if (null === $cell && null !== $project) {
            $box = null;
        }

        // If user validate the form, no ajax call, If the selected box and the selected cell is the same than the previous value
        if (null !== $cell && $this->box === $box && $this->cell === $cell) {
            // Transfert the cell to be kept in the list
            $previousCell = $this->cell;
        } else {
            $previousCell = null;
        }

        $this->addElement($form, $project, $box, $previousCell);
    }
}
