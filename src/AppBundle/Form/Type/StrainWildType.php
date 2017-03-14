<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\BiologicalOriginCategory;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class StrainWildType extends AbstractType
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
            ->add('biologicalOrigin', TextType::class, [
                'attr' => [
                    'placeholder' => 'Galeria melonella, Insect',
                    'data-help' => 'Where did you find it ?',
                ],
            ])
            ->add('source', TextType::class, [
                'attr' => [
                    'placeholder' => 'CBS, ...',
                    'data-help' => 'Who give it to you ?',
                ],
                'required' => false,
            ])
            ->add('address', TextType::class, [
                'attr' => [
                    'placeholder' => 'Pyramides, 75001 Paris, France',
                    'data-help' => 'The address with this format: Address, Zip code City, Country',
                ],
                'required' => false,
            ])
            ->add('country', CountryType::class, [
                'placeholder' => '-- Choose a country --',
                'required' => false,
            ])
            ->add('latitude', NumberType::class, [
                'scale' => 6,
                'attr' => [
                    'placeholder' => 48.866667,
                ],
                'required' => false,
            ])
            ->add('longitude', NumberType::class, [
                'scale' => 6,
                'attr' => [
                    'placeholder' => 2.333333,
                ],
                'required' => false,
            ])
        ;

        $formModifier = function (FormInterface $form, $team = null) {
            $form->add('biologicalOriginCategory', EntityType::class, [
                'class' => 'AppBundle\Entity\BiologicalOriginCategory',
                'query_builder' => function (EntityRepository $er) use ($team) {
                    return $er->createQueryBuilder('category')
                        ->leftJoin('category.team', 'team')
                        ->where('team = :team')
                        ->setParameter('team', $team)
                        ->orderBy('category.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => '-- Choose a category --',
                'label' => 'Category',
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $strain = $event->getData();

                // If it's a new strain, the default team is the FavoriteTeam of the user
                // else, we retrieve the team from the type
                if (null === $strain->getId()) {
                    $team = $this->tokenStorage->getToken()->getUser()->getFavoriteTeam();
                } else {
                    $team = $strain->getType()->getTeam();
                }

                $formModifier($event->getForm(), $team);
            }
        );

        $builder->get('team')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                $team = $event->getForm()->getData();

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback functions!
                $formModifier($event->getForm()->getParent(), $team);
            }
        );
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
