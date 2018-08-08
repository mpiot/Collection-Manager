<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StrainWildType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('biologicalOrigin', TextType::class, [
                'attr' => [
                    'placeholder' => 'Galeria melonella, Insect',
                    'data-help' => 'Where did you find it ?',
                ],
            ])
            ->add('source', TextType::class, [
                'attr' => [
                    'placeholder' => 'CBS, ...',
                    'data-help' => 'Who give it to you ?',
                ],
                'required' => false,
            ])
            ->add('address', TextType::class, [
                'attr' => [
                    'placeholder' => 'Pyramides, 75001 Paris, France',
                    'data-help' => 'The address with this format: Address, Zip code City, Country',
                ],
                'required' => false,
            ])
            ->add('country', CountryType::class, [
                'placeholder' => '-- Choose a country --',
                'required' => false,
            ])
            ->add('latitude', NumberType::class, [
                'scale' => 6,
                'attr' => [
                    'placeholder' => 48.866667,
                ],
                'required' => false,
            ])
            ->add('longitude', NumberType::class, [
                'scale' => 6,
                'attr' => [
                    'placeholder' => 2.333333,
                ],
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
            'data_class' => 'App\Entity\Strain',
        ]);
    }

    public function getParent()
    {
        return StrainType::class;
    }
}
