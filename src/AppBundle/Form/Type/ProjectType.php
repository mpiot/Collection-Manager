<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'attr' => array(
                    'placeholder' => 'Yarrowia lipolytica, populations genomics',
                ),
            ))
            ->add('prefix', TextType::class, array(
                'attr' => array(
                    'placeholder' => 'YALI',
                    'data-help' => 'Prefix used to name Strains and Boxes.',
                ),
            ))
            ->add('description', TextareaType::class, array(
                'attr' => array(
                    'placeholder' => 'A description about the project',
                ),
            ))
            ->add('teams', EntityType::class, array(
                'class' => 'AppBundle\Entity\Team',
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
            ))
            ->add('team_filter', EntityType::class, array(
                'class' => 'AppBundle\Entity\Team',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('team')
                        ->orderBy('team.name', 'ASC');
                },
                'choice_label' => 'name',
                'mapped' => false,
                'required' => false,
                'placeholder' => 'All teams',
                'attr' => array(
                    'data-filter-name' => 'team-filter',
                    'data-help' => 'Use this list to filter Administrators and Members checkboxes.',
                )
            ))
            ->add('administrators', EntityType::class, array(
                'class' => 'AppBundle\Entity\User',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->orderBy('user.username', 'ASC');
                },
                'choice_label' => 'username',
                'expanded' => true,
                'multiple' => true,
                'attr' => array(
                    'data-filtered-name' => 'administrators',
                    'data-filtered-by' => 'team-filter',
                ),
                'choice_attr' => function (User $user) {
                    return [
                        'data-teams' => '['.join(',', $user->getTeamsId()).']'
                    ];
                },
            ))
            ->add('members', EntityType::class, array(
                'class' => 'AppBundle\Entity\User',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->orderBy('user.username', 'ASC');
                },
                'choice_label' => 'username',
                'expanded' => true,
                'multiple' => true,
                'attr' => array(
                    'data-filtered-name' => 'members',
                    'data-filtered-by' => 'team-filter',
                ),
                'choice_attr' => function (User $user) {
                    return [
                        'data-teams' => '['.join(',', $user->getTeamsId()).']'
                    ];
                },
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Project',
        ));
    }
}
