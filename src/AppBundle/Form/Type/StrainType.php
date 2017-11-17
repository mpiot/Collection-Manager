<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Genus;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StrainType extends AbstractType
{
    protected $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('species', EntityType::class, [
                'class' => 'AppBundle\Entity\Species',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('species')
                        ->leftJoin('species.genus', 'genus')
                        ->addSelect('genus')
                        ->orderBy('genus.name', 'ASC')
                        ->addOrderBy('species.name', 'ASC');
                },
                'placeholder' => '-- select a species --',
                'choice_label' => 'scientificName',
                'label' => 'Species',
            ])
            ->add('name', TextType::class, [
                'attr' => [
                    'autocomplete' => 'off',
                    'data-help' => 'The name you want use to communicate about this strain.',
                ],
            ])
            ->add('comment')
            ->add('sequenced')
            ->add('tubes', CollectionType::class, [
                'entry_type' => StrainTubeType::class,
                'allow_add' => true,
                'allow_delete' => true,
                // Use add and remove properties in the entity
                'by_reference' => false,
            ])
        ;
    }
}
