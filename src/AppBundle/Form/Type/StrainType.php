<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Genus;
use AppBundle\Entity\Type;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class StrainType extends AbstractType
{
    protected $tokenStorage;

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
            ->add('team', EntityType::class, [
                'class' => 'AppBundle\Entity\Team',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('team')
                        ->leftJoin('team.members', 'members')
                        ->leftJoin('team.projects', 'projects')
                        ->leftJoin('projects.members', 'project_members')
                        ->where('members = :user')
                        ->orWhere('project_members = :user')
                        ->setParameter('user', $this->tokenStorage->getToken()->getUser());
                },
                'data' => $this->tokenStorage->getToken()->getUser()->getFavoriteTeam(),
                'choice_label' => 'name',
                'placeholder' => '-- select a team --',
                'mapped' => false,
            ])
            ->add('species', EntityType::class, [
                'class' => 'AppBundle\Entity\Species',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('species')
                        ->leftJoin('species.genus', 'genus')
                        ->addSelect('genus')
                        ->orderBy('genus.name', 'ASC')
                        ->addOrderBy('species.name', 'ASC');
                },
                'placeholder' => '-- select a species --',
                'choice_label' => 'scientificName',
                'label' => 'Species',
            ])
            ->add('name', TextType::class, [
                'attr' => [
                    'autocomplete' => 'off',
                    'data-help' => 'The name you want use to communicate about this strain.',
                ],
            ])
            ->add('comment')
            ->add('sequenced')
            ->add('deleted');

        $formModifier = function (FormInterface $form, $team = null) {
            $form->add('type', EntityType::class, [
                'class' => 'AppBundle\Entity\Type',
                'query_builder' => function (EntityRepository $er) use ($team) {
                    return $er->createQueryBuilder('types')
                        ->leftJoin('types.team', 'team')
                        ->where('team = :team')
                        ->setParameter('team', $team)
                        ->orderBy('types.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => '-- select a type --',
                'attr' => [
                    'data-help' => 'Which type of organism is it ?',
                ],
            ]);

            $form->add('tubes', CollectionType::class, [
                'entry_type' => StrainTubeType::class,
                'entry_options' => [
                    'parent_data' => $team,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                // Use add and remove properties in the entity
                'by_reference' => false,
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $form = $event->getForm();
                $strain = $event->getData();

                // If it's a new strain, the default team is the FavoriteTeam of the user
                // else, we retrieve the team from the type
                if (null === $strain->getId()) {
                    $team = $this->tokenStorage->getToken()->getUser()->getFavoriteTeam();
                } else {
                    // We set the team
                    $team = $strain->getType()->getTeam();

                    //And, we remove the team field
                    $form->remove('team');
                }

                $formModifier($form, $team);
            }
        );

        $builder->get('team')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                $team = $event->getForm()->getData();

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback functions!
                $formModifier($event->getForm()->getParent(), $team);
            }
        );
    }
}
