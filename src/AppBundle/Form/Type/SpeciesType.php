<?php

namespace AppBundle\Form\Type;

use AppBundle\Form\Type\GenusSelectorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpeciesType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('taxId', IntegerType::class, [
                'attr' => [
                    'data-help' => 'The taxID of the species, you can find it <a target="_blank" href="https://www.ncbi.nlm.nih.gov/taxonomy">here</a>.',
                ],
                'required' => false,
            ])
            ->add('genus', GenusSelectorType::class)
            ->add('name', TextType::class, [
                'label' => 'Species',
            ])
            ->add('synonyms', CollectionType::class, [
                'entry_type' => SpeciesSynonymType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Species',
        ]);
    }
}
