<?php

namespace AppBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\Count;

class AdvancedSearchType extends AbstractType
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
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
            ->add('search', SearchType::class, [
                'required' => false,
                'attr' => [
                    'autocomplete' => 'off',
                ],
            ])
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Gmo' => 'gmo',
                    'Wild' => 'wild',
                    'Plasmid' => 'plasmid',
                    'Primer' => 'primer',
                ],
                'expanded' => true,
                'multiple' => true,
                'data' => ['gmo', 'wild', 'plasmid', 'primer'],
                'constraints' => [
                    new Count(['min' => 1, 'minMessage' => 'Select at least one element.']),
                ],
            ])
            ->add('country', CountryType::class, [
                'placeholder' => 'All countries',
                'required' => false,
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
                'placeholder' => 'All teams',
                'required' => false,
            ])
            ->add('author', EntityType::class, [
                'class' => 'AppBundle\Entity\User',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->leftJoin('user.teams', 'teams')
                        ->where('teams IN (:teams)')
                            ->setParameter('teams', $this->tokenStorage->getToken()->getUser()->getTeams())
                        ->orderBy('user.firstName', 'ASC')
                        ->addOrderBy('user.lastName', 'ASC');
                },
                'choice_label' => 'fullName',
                'placeholder' => 'All users',
                'required' => false,
            ])
        ;
    }
}
