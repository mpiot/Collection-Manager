<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SellerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('offerReference', TextType::class, [
                'required' => false,
            ])
            ->add('addOfferFile', ChoiceType::class, [
                'choices' => [
                    'No' => 0,
                    'Yes' => 1,
                ],
                'multiple' => false,
                'expanded' => true,
                'label' => 'Send a file ?',
            ])
            ->add('offerFile', UploadFileType::class, [
                'required' => false,
            ])
        ;

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $seller = $event->getData();

                if (null !== $seller && null !== $seller->getOfferFile()) {
                    if (null !== $seller->getOfferFile()->getPath()) {
                        $seller->setAddOfferFile(true);
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
            'data_class' => 'AppBundle\Entity\Seller',
        ]);
    }
}
