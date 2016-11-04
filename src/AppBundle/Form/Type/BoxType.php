<?php

namespace AppBundle\Form\Type;

use AppBundle\Repository\ProjectRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class BoxType extends AbstractType
{
    private $tokenStorage;

    public function __construct(TokenStorage $tokenStorage)
    {
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
                        ->leftJoin('project.members', 'members')
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
            ->add('type', EntityType::class, array(
                'class' => 'AppBundle\Entity\Type',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('types')
                        ->orderBy('types.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => '-- select a type --',
                'attr' => array(
                    'data-help' => 'Define the type of organisms that are in the box (just as indication).',
                )
            ))
            ->add('freezer', TextType::class, array(
                'attr' => array(
                    'placeholder' => 'Emile',
                    'data-help' => 'In which freezer is the box ?',
                ),
            ))
            ->add('location', TextType::class, array(
                'label' => 'Location in the freezer',
                'attr' => array(
                    'placeholder' => '1st shelf on the top, 3rd rack on the left',
                    'data-help' => 'Where is the box in the freezer ?',
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
