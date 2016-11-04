<?php

namespace AppBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class WildStrainType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('biologicalOriginCategory', EntityType::class, array(
                'class' => 'AppBundle\Entity\BiologicalOriginCategory',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('category')
                        ->orderBy('category.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => '-- Choose a category --',
                'label' => 'Category',
            ))
            ->add('biologicalOrigin', TextType::class, array(
                'attr' => array(
                    'placeholder' => 'Galeria melonella, Insect',
                    'data-help' => 'Where did you find it ?',
                ),
            ))
            ->add('source', TextType::class, array(
                'attr' => array(
                    'placeholder' => 'CBS, ...',
                    'data-help' => 'Who give it to you ?',
                ),
                'required' => false,
            ))
            ->add('address', TextType::class, array(
                'attr' => array(
                    'placeholder' => 'Pyramides, 75001 Paris, France',
                    'data-help' => 'The address with this format: Address, Zip code City, Country',
                ),
            ))
            ->add('country', CountryType::class, array(
                'placeholder' => '-- Choose a country --',
            ))
            ->add('latitude', NumberType::class, array(
                'scale' => 6,
                'attr' => array(
                    'placeholder' => 48.866667,
                ),
                'required' => false,
            ))
            ->add('longitude', NumberType::class, array(
                'scale' => 6,
                'attr' => array(
                    'placeholder' => 2.333333,
                ),
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
            'data_class' => 'AppBundle\Entity\WildStrain',
        ));
    }

    public function getParent()
    {
        return StrainType::class;
    }
}
