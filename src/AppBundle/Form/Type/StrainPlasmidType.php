<?php

namespace AppBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class StrainPlasmidType extends AbstractType
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
            ->add('plasmid', EntityType::class, array(
                'class' => 'AppBundle\Entity\Plasmid',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->leftJoin('p.team', 'team')
                        ->leftJoin('team.members', 'members')
                        ->where('members = :user')
                        ->setParameter('user', $this->tokenStorage->getToken()->getUser())
                        ->orderBy('p.autoName', 'ASC')
                    ;
                },
                'choice_label' => function($plasmid) {
                    return $plasmid->getAutoName().' - '.$plasmid->getName();
                },
                'placeholder' => '-- select a plasmid --',
            ))
            ->add('state', ChoiceType::class, array(
                'choices' => array(
                    'Replicative' => 'replicative',
                    'Integrated' => 'integrated',
                    'Cured' => 'cured'
                ),
                'placeholder' => '-- select a state --',
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\StrainPlasmid',
        ));
    }
}
