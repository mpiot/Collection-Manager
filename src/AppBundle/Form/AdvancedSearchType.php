<?php

namespace AppBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Validator\Constraints\Count;

class AdvancedSearchType extends AbstractType
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
            ->add('search', TextType::class, array(
                'required' => false,
            ))
            ->add('strainCategory', ChoiceType::class, array(
                'choices' => array(
                    'Gmo' => 'gmo',
                    'Wild' => 'wild',
                ),
                'expanded' => true,
                'multiple' => true,
                'data' => ['gmo', 'wild'],
                'constraints' => array(
                    new Count(array('min' => 1, 'minMessage' => 'Select at least one element.')),
                ),
            ))
            ->add('country', CountryType::class, array(
                'placeholder' => '-- Choose a country --',
                'required' => false,
            ))
            ->add('project', EntityType::class,array(
                'class' => 'AppBundle\Entity\Project',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('project')
                        ->leftJoin('project.teams', 'teams')
                        ->leftJoin('teams.members', 'members')
                        ->where('members = :user')
                        ->setParameter('user', $this->tokenStorage->getToken()->getUser())
                        ->orderBy('teams.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => 'All available projects',
                'required' => false,
            ))
            ->add('type', EntityType::class,array(
                'class' => 'AppBundle\Entity\Type',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('type')
                        ->leftJoin('type.team', 'teams')
                        ->leftJoin('teams.members', 'members')
                        ->where('members = :user')
                        ->setParameter('user', $this->tokenStorage->getToken()->getUser())
                        ->orderBy('type.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => 'All types',
                'required' => false,
            ))
            ->add('deleted', CheckboxType::class, array(
                'label' => 'Search deleted strains ?',
                'required' => false,
            ))
        ;
    }
}
