<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Genus;
use AppBundle\Entity\Type;
use Doctrine\ORM\EntityManager;
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
            ->add('species', StrainSpeciesType::class)
            ->add('type', EntityType::class, [
                'class' => 'AppBundle\Entity\Type',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('types')
                        ->leftJoin('types.team', 'team')
                        ->leftJoin('team.members', 'members')
                        ->where('members = :user')
                            ->setParameter('user', $this->tokenStorage->getToken()->getUser())
                        ->orderBy('types.name', 'ASC');
                },
                'group_by' => function (Type $type) {
                    return $type->getTeam()->getName();
                },
                'choice_label' => 'name',
                'placeholder' => '-- select a type --',
                'attr' => [
                    'data-help' => 'Which type of organism is it ?',
                ],
            ])
            ->add('name', TextType::class, [
                'attr' => [
                    'autocomplete' => 'off',
                    'data-help' => 'The name you want use to communicate about this strain.',
                ],
            ])
            ->add('comment')
            ->add('sequenced')
            ->add('deleted')
            ->add('tubes', CollectionType::class, [
                'entry_type' => StrainTubeType::class,
                'allow_add' => true,
                'allow_delete' => true,
                // Use add and remove properties in the entity
                'by_reference' => false,
            ]);
    }
}
