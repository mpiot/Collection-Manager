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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class TubeDynamicFieldSubscriber implements EventSubscriberInterface
{
    private $em;
    private $tokenStorage;
    private $box;
    private $cell;
    private $disabled;

    public function __construct(EntityManager $entityManager, TokenStorage $tokenStorage)
    {
        $this->em = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
        ];
    }

    protected function addElement(FormInterface $form, Project $project = null, Box $box = null, $previousCell = null)
    {
        $form->add('project', EntityType::class, [
            'class' => 'AppBundle\Entity\Project',
            'query_builder' => function (EntityRepository $pr) {
                return $pr->createQueryBuilder('project')
                    ->leftJoin('project.members', 'members')
                    ->where('members = :user')
                        ->setParameter('user', $this->tokenStorage->getToken()->getUser())
                    ->orderBy('project.name', 'ASC');
            },
            'choice_label' => 'name',
            'placeholder' => '-- select a project --',
            //'data' => $project,
            'disabled' => $this->disabled,
        ]);

        $form->add('box', EntityType::class, [
            'class' => 'AppBundle\Entity\Box',
            'choice_label' => 'name',
            'placeholder' => '-- select a box --',
            'query_builder' => function (EntityRepository $er) use ($project) {
                return $er->createQueryBuilder('b')
                        ->leftJoin('b.project', 'p')
                        ->where('p = :project')
                            ->setParameter('project', $project);
            },
            //'data' => $box,
            'disabled' => $this->disabled,
        ]);

        $cells = null !== $box ? $box->getEmptyCells($previousCell) : null;

        $form->add('cell', ChoiceType::class, [
            'placeholder' => '-- select a cell --',
            'choices' => $cells,
            //'data' => $previousCell,
            'disabled' => $this->disabled,
        ]);
    }

    public function onPreSetData(FormEvent $event)
    {
        // Store form and data
        $form = $event->getForm();
        $data = $event->getData();

        // The tube is $data
        $tube = $data;

        // If it's a new tube, disable the fields
        if (null !== $tube && $tube->getId()) {
            $this->disabled = true;
        }

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
