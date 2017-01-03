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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ProjectType extends AbstractType
{
    private $tokenStorage;

    public function __construct(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Yarrowia lipolytica, populations genomics',
                ],
            ])
            ->add('prefix', TextType::class, [
                'attr' => [
                    'placeholder' => 'YALI',
                    'data-help' => 'Prefix used to name Strains and Boxes.',
                ],
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'A description about the project',
                ],
            ])
            ->add('team', EntityType::class, [
                'class' => 'AppBundle\Entity\Team',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('team')
                        ->leftJoin('team.members', 'members')
                        ->where('members = :user')
                        ->setParameter('user', $this->tokenStorage->getToken()->getUser())
                        ->orderBy('team.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => '-- Select a team --',
                'multiple' => false,
            ])
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
                ],
            ])
            ->add('administrators', EntityType::class, [
                'class' => 'AppBundle\Entity\User',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->orderBy('user.username', 'ASC');
                },
                'choice_label' => 'username',
                'expanded' => true,
                'multiple' => true,
                'attr' => [
                    'data-filtered-name' => 'administrators',
                    'data-filtered-by' => 'team-filter',
                ],
                'choice_attr' => function (User $user) {
                    return [
                        'data-teams' => '['.implode(',', $user->getTeamsId()).']',
                    ];
                },
            ])
            ->add('members', EntityType::class, [
                'class' => 'AppBundle\Entity\User',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->orderBy('user.username', 'ASC');
                },
                'choice_label' => 'username',
                'expanded' => true,
                'multiple' => true,
                'attr' => [
                    'data-filtered-name' => 'members',
                    'data-filtered-by' => 'team-filter',
                ],
                'choice_attr' => function (User $user) {
                    return [
                        'data-teams' => '['.implode(',', $user->getTeamsId()).']',
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
            'data_class' => 'AppBundle\Entity\Project',
        ]);
    }
}
