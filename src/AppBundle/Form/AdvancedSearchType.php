<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class AdvancedSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('search', TextType::class, array(
                'constraints' => array(
                    new NotBlank(),
                )
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
                    new Count(array('min' => 1, 'minMessage' => 'Select at least one element.'))
                )
            ))
            ->add('country', CountryType::class, array(
                'placeholder' => '-- Choose a country --',
                'required' => false,
            ))
            ->add('deleted', CheckboxType::class, array(
                'label'    => 'Search deleted strains ?',
                'required' => false,
            ))
        ;
    }
}
