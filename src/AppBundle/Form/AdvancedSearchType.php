<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdvancedSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('search', TextType::class)
            ->add('strainCategory', ChoiceType::class, array(
                'choices' => array(
                    'Gmo' => 'gmo',
                    'Wild' => 'wild',
                ),
                'expanded' => true,
                'multiple' => true,
                'data' => ['gmo', 'wild'],
            ))
        ;
    }
}
