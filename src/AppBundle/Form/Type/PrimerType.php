<?php

namespace AppBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PrimerType extends AbstractType
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
            ->add('team', EntityType::class, [
                'class' => 'AppBundle\Entity\Team',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('team')
                        ->leftJoin('team.members', 'members')
                        ->where('members = :user')
                            ->setParameter('user', $this->tokenStorage->getToken()->getUser())
                        ->orderBy('team.name', 'ASC');
                },
                'data' => $this->tokenStorage->getToken()->getUser()->getFavoriteTeam(),
            ])
            ->add('name', TextType::class, [
                'attr' => [
                    'data-help' => 'The name you want to use.',
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('orientation', ChoiceType::class, [
                'choices' => [
                    'Forward' => 'forward',
                    'Reverse' => 'reverse',
                ],
                'multiple' => false,
                'expanded' => true,
                'required' => false,
            ])
            ->add('hybridationTemp', NumberType::class, [
                'scale' => 1,
                'required' => false,
            ])
            ->add('sequence', TextType::class, [
                'label' => 'Match sequence',
            ])
            ->add('fivePrimeExtension', TextType::class, [
                'label' => '5\' Extension sequence',
                'required' => false,
            ])
            ->add('labelMarker', TextType::class, [
                'label' => 'Label/Marker',
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
            'data_class' => 'AppBundle\Entity\Primer',
        ]);
    }
}
