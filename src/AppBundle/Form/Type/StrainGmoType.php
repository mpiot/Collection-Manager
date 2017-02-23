<?php

namespace AppBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StrainGmoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('genotype', TextareaType::class, [
                'required' => false,
            ])
            ->add('strainPlasmids', CollectionType::class, [
                'entry_type' => StrainPlasmidType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
            ])
            ->add('parents', CollectionType::class, [
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => 'AppBundle\Entity\Strain',
                    'choice_label' => 'fullName',
                    'placeholder' => '-- select a parent --',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('strain')
                            ->orderBy('strain.autoName', 'ASC');
                    },
                ],
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
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
            'data_class' => 'AppBundle\Entity\Strain',
        ]);
    }

    public function getParent()
    {
        return StrainType::class;
    }
}
