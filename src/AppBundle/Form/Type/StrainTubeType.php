<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Box;
use AppBundle\Entity\Project;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class StrainTubeType extends AbstractType
{
    private $em;
    private $tokenStorage;
    private $previousTubes = [];

    public function __construct(EntityManager $entityManager, TokenStorage $tokenStorage)
    {
        $this->em = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $project = null === $data ? null : $data->getProject();
        $box = null === $data ? null : $data->getBox();
        $cell = null === $data ? null : $data->getCell();

        if (null !== $data) {
            $this->previousTubes[] = $data;
        }

        // Add forms
        $this->addProjectForm($form, $form->getConfig()->getOptions());
        $this->addBoxForm($form, $project);
        $this->addCellForm($form, $box, $cell);
    }

    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        $persistedTube = $event->getForm()->getData();

        $project = null === $data['project'] ? null : $this->em->getRepository('AppBundle:Project')->findOneById($data['project']);
        $box = null === $data['box'] ? null : $this->em->getRepository('AppBundle:Box')->findOneById($data['box']);
        $cell = null;

        if (false !== $key = array_search($persistedTube, $this->previousTubes, 1)) {
            $previousTube = $this->previousTubes[$key];

            if ($box === $previousTube->getBox()) {
                $cell = $previousTube->getCell();
            }
        }

        $this->addProjectForm($form, $form->getConfig()->getOptions());
        $this->addBoxForm($form, $project);
        $this->addCellForm($form, $box, $cell);
    }

    protected function addProjectForm(FormInterface $form, $options)
    {
        $form->add('project', EntityType::class, [
            'class' => 'AppBundle\Entity\Project',
            'query_builder' => function (EntityRepository $pr) use ($options) {
                return $pr->createQueryBuilder('project')
                    ->leftJoin('project.team', 'team')
                    ->leftJoin('project.members', 'members')
                    ->where('team = :team')
                    ->andWhere('members = :user')
                    ->andWhere('project.valid = true')
                    ->setParameters([
                        'team' => $options['parent_data'],
                        'user' => $this->tokenStorage->getToken()->getUser(),
                    ])
                    ->orderBy('project.name', 'ASC');
            },
            'choice_label' => 'name',
            'placeholder' => '-- select a project --',
        ]);
    }

    protected function addBoxForm(FormInterface $form, Project $project = null)
    {
        $form->add('box', EntityType::class, [
            'class' => 'AppBundle:Box',
            'placeholder' => '-- select a box --',
            'query_builder' => function (EntityRepository $er) use ($project) {
                return $er->createQueryBuilder('b')
                    ->where('b.project = :project')
                        ->setParameter('project', $project);
            },
            'choice_label' => function ($val) {
                return (0 === $val->getFreeSpace()) ? $val->getName().' (full)' : $val->getName();
            },
            'choice_attr' => function ($val) {
                return (0 === $val->getFreeSpace()) ? ['disabled' => 'disabled'] : [];
            },
            'auto_initialize' => false,
        ]);
    }

    protected function addCellForm(FormInterface $form, Box $box = null, $previousCell = null)
    {
        $cells = null === $box ? null : $box->getEmptyCells($previousCell);

        $form->add('cell', ChoiceType::class, [
            'choices' => $cells,
            'placeholder' => '-- select a cell --',
            'auto_initialize' => false,
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Tube',
        ]);

        $resolver->setRequired(['parent_data']);
    }
}
