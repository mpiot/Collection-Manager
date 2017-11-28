<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Location;
use AppBundle\Repository\LocationRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EquipmentType extends AbstractType
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('group', EntityType::class, [
                'class' => 'AppBundle\Entity\Group',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('g')
                        ->leftJoin('g.members', 'members')
                        ->where('members = :user')
                        ->setParameter('user', $this->tokenStorage->getToken()->getUser())
                        ->orderBy('g.name', 'ASC');
                },
                'choice_label' => 'name',
                'data' => $this->tokenStorage->getToken()->getUser()->getFavoriteGroup(),
            ])
            ->add('name')
            ->add('description')
            ->add('serialNumber')
            ->add('purchaseDate', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('brand', EntityType::class, [
                'class' => 'AppBundle\Entity\Brand',
                'choice_label' => 'name',
            ])
            ->add('model')
            ->add('seller', EntityType::class, [
                'class' => 'AppBundle\Entity\Seller',
                'choice_label' => 'name',
            ])
            ->add('inventoryNumber')
            ->add('location', EntityType::class, [
                'class' => 'AppBundle\Entity\Location',
                'query_builder' => function (LocationRepository $er) {
                    return $er->createQueryBuilder('location')
                        ->leftJoin('location.root', 'root')
                        ->addSelect('root')
                        ->orderBy('location.lft', 'ASC')
                        ->where('root.name = :location')
                        ->andWhere('location.lvl != 0')
                        ->setParameter('location', 'Location');
                },
                'choice_label' => function (Location $location) {
                    return str_repeat('-', ($location->getLevel() - 1)).$location->getName();
                },
                'placeholder' => '-- select a location --',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Equipment',
        ]);
    }
}
