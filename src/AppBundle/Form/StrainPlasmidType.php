<?php

namespace AppBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StrainPlasmidType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('plasmid', EntityType::class, array(
                'class' => 'AppBundle\Entity\Plasmid',
                'choice_label' => 'name',
                'placeholder' => '-- select a plasmid --',
            ))
            ->add('state', ChoiceType::class, array(
                'choices' => array(
                    'Replicative' => 'replicative',
                    'Integrative' => 'integrative',
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
