<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Plasmid;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StrainPlasmidType extends AbstractType
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
            ->add('plasmid', EntityType::class, [
                'class' => 'AppBundle\Entity\Plasmid',
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('plasmid')
                        ->leftJoin('plasmid.team', 'team')
                        ->leftJoin('team.members', 'members')
                        ->where('members = :user')
                            ->setParameter('user', $this->tokenStorage->getToken()->getUser())
                        ->orderBy('plasmid.autoName', 'ASC')
                    ;
                },
                'choice_label' => function (Plasmid $plasmid) {
                    return $plasmid->getAutoName().' - '.$plasmid->getName();
                },
                'placeholder' => '-- select a plasmid --',
            ])
            ->add('state', ChoiceType::class, [
                'choices' => [
                    'Replicative' => 'replicative',
                    'Integrated' => 'integrated',
                    'Cured' => 'cured',
                ],
                'placeholder' => '-- select a state --',
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\StrainPlasmid',
        ]);
    }
}
