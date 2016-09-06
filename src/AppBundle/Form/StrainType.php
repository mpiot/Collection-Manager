<?php

namespace AppBundle\Form;

use AppBundle\Entity\Genus;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StrainType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('genus', EntityType::class, array(
                'class'    => 'AppBundle\Entity\Genus',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('g')
                        ->orderBy('g.genus', 'ASC');
                },
                'choice_label' => 'genus',
                'placeholder' => '-- select a genus --',
                'mapped' => false,
                'required' => false,
            ))
            ->add('type', EntityType::class, array(
                'class' => 'AppBundle\Entity\Type',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => '-- select a type --',
            ))
            ->add('usualName')
            ->add('comment')
            ->add('sequenced')
            ->add('deleted')
            ->add('tubes', CollectionType::class, array(
                'entry_type' => TubeType::class,
                'allow_add' => true,
                'allow_delete' => true,
                // Use add and remove properties in the entity
                'by_reference' => false,
            ))
        ;

        // Modifiers

        $genusModifier = function (FormInterface $form, $genus) {
            $form->add('genus', EntityType::class, array(
                'class'    => 'AppBundle\Entity\Genus',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('g')
                        ->orderBy('g.genus', 'ASC');
                },
                'choice_label' => 'genus',
                'placeholder' => '-- select a genus --',
                'mapped' => false,
                'required' => false,
                'data' => $genus,
            ));
        };

        $speciesModifier = function (FormInterface $form, $genus) {
            $form->add('species', EntityType::class, array(
                'class' => 'AppBundle\Entity\Species',
                'query_builder' => function (EntityRepository $er) use ($genus) {
                    return $er->createQueryBuilder('s')
                        ->where('s.genus = :genus')
                        ->setParameter('genus', $genus)
                        ->orderBy('s.species', 'ASC');
                },
                'placeholder' => '-- select a species --',
                'choice_label' => 'species',
            ));
        };

        // Listeners

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($speciesModifier, $genusModifier) {
                $form = $event->getForm();
                $data = $event->getData();

                dump($form->getErrors(true, false));
                dump($data);

                if (null !== $species = $data->getSpecies()) {
                    //$genusModifier($form, $species->getGenus());
                    $speciesModifier($form, $species->getGenus());
                } else {
                    $speciesModifier($form, null);
                }
            }
        );

        $builder->get('genus')->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event) use ($speciesModifier) {
                $genus = $event->getData();

                $speciesModifier($event->getForm()->getParent(), $genus);
            }
        );
    }
}
