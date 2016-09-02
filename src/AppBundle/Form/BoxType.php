<?php

namespace AppBundle\Form;

use AppBundle\Repository\ProjectRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BoxType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('project', EntityType::class, array(
                'class' => 'AppBundle\Entity\Project',
                'query_builder' => function (ProjectRepository $pr) {
                    return $pr->createQueryBuilder('p')
                        ->orderBy('p.name', 'ASC');
                },
                'choice_label' => 'name',
            ))
            ->add('name')
            ->add('description')
            ->add('type', EntityType::class, array(
                'class' => 'AppBundle\Entity\Type',
                'choice_label' => 'name',
                'placeholder' => '-- select a type --',
            ))
            ->add('freezer')
            ->add('location')
            ->add('colNumber')
            ->add('rowNumber')
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Box'
        ));
    }
}
