<?php

namespace AppBundle\Form;

use AppBundle\Entity\Genus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GmoStrainType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description')
            ->add('genotype')
            ->add('strainPlasmids', CollectionType::class, array(
                'entry_type' => StrainPlasmidType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\GmoStrain'
        ));
    }

    public function getParent()
    {
        return StrainType::class;
    }
}
