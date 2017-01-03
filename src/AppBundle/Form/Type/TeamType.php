<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('team_filter', EntityType::class, [
                'class' => 'AppBundle\Entity\Team',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('team')
                        ->orderBy('team.name', 'ASC');
                },
                'choice_label' => 'name',
                'mapped' => false,
                'required' => false,
                'placeholder' => 'All teams',
                'attr' => [
                    'data-filter-name' => 'team-filter',
                    'data-help' => 'Use this list to filter Administrators and Members checkboxes.',
                ]
            ])
            ->add('administrators', EntityType::class, [
                'class' => 'AppBundle\Entity\User',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.username', 'ASC');
                },
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'data-filtered-name' => 'administrators',
                    'data-filtered-by' => 'team-filter',
                ],
                'choice_attr' => function (User $user) {
                    return [
                        'data-teams' => '['.join(',', $user->getTeamsId()).']'
                    ];
                },
            ])
            ->add('members', EntityType::class, [
                'class' => 'AppBundle\Entity\User',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.username', 'ASC');
                },
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'attr' => [
                    'data-filtered-name' => 'members',
                    'data-filtered-by' => 'team-filter',
                ],
                'choice_attr' => function (User $user) {
                    return [
                        'data-teams' => '['.join(',', $user->getTeamsId()).']'
                    ];
                },
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Team',
        ]);
    }
}
