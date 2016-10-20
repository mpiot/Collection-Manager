<?php

namespace AppBundle\Form;

use AppBundle\Entity\Project;
use AppBundle\Repository\ProjectRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class BoxType extends AbstractType
{
    private $em;
    private $tokenStorage;

    public function __construct(EntityManager $em, TokenStorage $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('project', EntityType::class, array(
                'class' => 'AppBundle\Entity\Project',
                'query_builder' => function (ProjectRepository $pr) {
                    return $pr->createQueryBuilder('project')
                        ->leftJoin('project.teams', 'teams')
                        ->leftJoin('teams.members', 'members')
                        ->where('members = :user')
                            ->setParameter('user', $this->tokenStorage->getToken()->getUser())
                        ->orderBy('project.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => '-- select a project --',
            ))
            ->add('name', TextType::class, array(
                'attr' => array(
                    'placeholder' => 'Name of the box',
                ),
            ))
            ->add('description', TextareaType::class, array(
                'attr' => array(
                    'placeholder' => 'Description about the box',
                ),
            ))
/*
            ->add('type', EntityType::class, array(
                'class' => 'AppBundle\Entity\Type',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('types')
                        ->leftJoin('types.team', 'team')
                        ->leftJoin('team.members', 'members')
                        ->where('members = :user')
                        ->setParameter('user', $this->tokenStorage->getToken()->getUser())
                        ->orderBy('types.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => '-- select a type --',
            ))
*/
            ->add('freezer', TextType::class, array(
                'attr' => array(
                    'placeholder' => 'In which freezer is the box: Emile',
                ),
            ))
            ->add('location', TextType::class, array(
                'label' => 'Location in the freezer',
                'attr' => array(
                    'placeholder' => '1st shelf on the top, 3rd rack on the left',
                ),
            ))
            ->add('colNumber', NumberType::class, array(
                'label' => 'Number of columns',
                'attr' => array(
                    'placeholder' => '10',
                ),
            ))
            ->add('rowNumber', NumberType::class, array(
                'label' => 'Number of rows',
                'attr' => array(
                    'placeholder' => '10',
                ),
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }

    protected function addTypeElement(FormInterface $form, Project $project = null)
    {
        dump('plop');
        $form->add('type', EntityType::class, array(
            'class' => 'AppBundle\Entity\Type',
            'query_builder' => function (EntityRepository $er) use ($project) {
                return $er->createQueryBuilder('types')
                    ->leftJoin('types.team', 'team')
                    ->leftJoin('team.projects', 'projects')
                    ->where('projects = :project')
                    ->setParameter('project', $project)
                    ->orderBy('types.name', 'ASC');
            },
            'choice_label' => 'name',
            'placeholder' => '-- select a type --',
        ));
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $box = $event->getData();

        dump('p');

        $this->addTypeElement($form, $box->getProject());
    }

    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        // If the user want select a species
        if (isset($data['project'])) {
            $project = $this->em->getRepository('AppBundle:Project')->findOneById($data['project']);
            $this->addTypeElement($form, $project);
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Box',
        ));
    }
}
