<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Location;
use AppBundle\Repository\LocationRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;

class ProductType extends AbstractType
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array
     */
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
            ->add('brand', EntityType::class, [
                'class' => 'AppBundle\Entity\Brand',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('brand')
                        ->orderBy('brand.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => '-- select a brand --',
            ])
            ->add('brandReference')
            ->add('seller', EntityType::class, [
                'class' => 'AppBundle\Entity\Seller',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('seller')
                        ->orderBy('seller.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => '-- select a seller --',
            ])
            ->add('sellerReference')
            ->add('catalogPrice', MoneyType::class)
            ->add('negotiatedPrice', MoneyType::class)
            ->add('quoteFile', VichFileType::class, [
                'required' => false,
                'allow_delete' => false,
                'download_uri' => false,
                'download_label' => false,
            ])
            ->add('manualFile', VichFileType::class, [
                'required' => false,
                'allow_delete' => false,
                'download_uri' => false,
                'download_label' => false,
            ])
            ->add('packedBy')
            ->add('packagingUnit')
            ->add('storageUnit')
            ->add('stockWarningAlert')
            ->add('stockDangerAlert')
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Product',
        ]);
    }
}
