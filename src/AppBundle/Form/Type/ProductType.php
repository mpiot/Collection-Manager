<?php

namespace AppBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ProductType extends AbstractType
{
    private $tokenStorage;

    public function __construct(TokenStorage $tokenStorage)
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
            ->add('team', EntityType::class, [
                'class' => 'AppBundle\Entity\Team',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('team')
                        ->leftJoin('team.members', 'members')
                        ->where('members = :user')
                            ->setParameter('user', $this->tokenStorage->getToken()->getUser())
                        ->orderBy('team.name', 'ASC');
                },
                'choice_label' => 'name',
                'data' => $this->tokenStorage->getToken()->getUser()->getFavoriteTeam(),
            ])
            ->add('name')
            ->add('location')
            ->add('brand', EntityType::class, [
                'class' => 'AppBundle\Entity\Brand',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('brand')
                        ->orderBy('brand.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => 'Select a brand',
            ])
            ->add('brandReference')
            ->add('seller', EntityType::class, [
                'class' => 'AppBundle\Entity\Seller',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('seller')
                        ->orderBy('seller.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => 'Select a seller',
            ])
            ->add('sellerReference')
            ->add('catalogPrice', MoneyType::class)
            ->add('negotiatedPrice', MoneyType::class)
            ->add('addQuoteFile', ChoiceType::class, [
                'choices' => [
                    'No' => 0,
                    'Yes' => 1,
                ],
                'multiple' => false,
                'expanded' => true,
                'label' => 'Send a quote file ?',
            ])
            ->add('quoteFile', DocumentFileType::class, [
                'required' => false,
            ])
            ->add('addManualFile', ChoiceType::class, [
                'choices' => [
                    'No' => 0,
                    'Yes' => 1,
                ],
                'multiple' => false,
                'expanded' => true,
                'label' => 'Send a manual file ?',
            ])
            ->add('manualFile', DocumentFileType::class, [
                'required' => false,
            ])
            ->add('packedBy')
            ->add('packagingUnit')
            ->add('storageUnit')
            ->add('stockWarningAlert')
            ->add('stockDangerAlert')
        ;

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $product = $event->getData();

                if (null !== $product && null !== $product->getQuoteFile()) {
                    if (null !== $product->getQuoteFile()->getPath()) {
                        $product->setAddQuoteFile(true);
                    }
                }

                if (null !== $product && null !== $product->getManualFile()) {
                    if (null !== $product->getManualFile()->getPath()) {
                        $product->setAddManualFile(true);
                    }
                }
            }
        );
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
