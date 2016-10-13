<?php

namespace AppBundle\Form;

use AppBundle\Repository\GenusRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
            ->add('genus', EntityType::class, array(
                'class' => 'AppBundle\Entity\Genus',
                'query_builder' => function (GenusRepository $gr) {
                    return $gr->createQueryBuilder('g')
                        ->orderBy('g.genus', 'ASC');
                },
                'choice_label' => 'genus',
            ))
            ->add('species', TextType::class, array(
                'attr' => array(
                    'placeholder' => 'lipolytica',
                ),
            ))
            ->add('synonyms', CollectionType::class, array(
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
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
            'data_class' => 'AppBundle\Entity\Species',
        ));
    }
}
