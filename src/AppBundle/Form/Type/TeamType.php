<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class TeamType extends AbstractType
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
            ->add('name', TextType::class)
            ->add('administrators', EntityType::class, [
                'class' => 'AppBundle\Entity\User',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.lastName', 'ASC')
                        ->addOrderBy('u.firstName', 'ASC');
                },
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'data-filtered-name' => 'administrators',
                    'data-filtered-by' => 'team-filter',
                ],
                'choice_attr' => function (User $user) {
                    return [
                        'data-teams' => '['.implode(',', $user->getTeamsId()).']',
                    ];
                },
                'choice_label' => function (User $user) {
                    return $user->getLastName().' '.$user->getFirstName();
                },
            ])
            ->add('members', EntityType::class, [
                'class' => 'AppBundle\Entity\User',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.lastName', 'ASC')
                        ->addOrderBy('u.firstName', 'ASC');
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
                        'data-teams' => '['.implode(',', $user->getTeamsId()).']',
                    ];
                },
                'choice_label' => function (User $user) {
                    return $user->getLastName().' '.$user->getFirstName();
                },
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $team = $event->getData();

            $defaultTeam = null !== $team->getId() ? $team : null;

            $form->add('team_filter', EntityType::class, [
                'class' => 'AppBundle\Entity\Team',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('team')
                        ->orderBy('team.name', 'ASC');
                },
                'choice_label' => 'name',
                'mapped' => false,
                'required' => false,
                'placeholder' => 'Users without team',
                'attr' => [
                    'data-filter-name' => 'team-filter',
                    'data-help' => 'Use this list to filter Administrators and Members checkboxes.',
                ],
                'data' => $defaultTeam,
            ]);
        });
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
