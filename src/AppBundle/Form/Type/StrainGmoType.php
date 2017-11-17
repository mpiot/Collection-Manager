<?php

namespace AppBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StrainGmoType extends AbstractType
{
    private $tokenStorage;
    private $strainId;

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
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('genotype', TextareaType::class, [
                'required' => false,
            ])
        ;

        $formModifier = function (FormInterface $form, $team = null, $strainId = null) {
            $form->add('parents', CollectionType::class, [
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => 'AppBundle\Entity\Strain',
                    'choice_label' => 'fullName',
                    'placeholder' => '-- select a parent --',
                    'query_builder' => function (EntityRepository $er) use ($team, $strainId) {
                        return $er->createQueryBuilder('strain')
                            ->leftJoin('strain.team', 'team')
                            ->leftJoin('team.members', 'members')
                            ->where('members = :user')
                            ->andWhere('strain.id <> :strainId')
                            ->andWhere('team = :team')
                                ->setParameters([
                                    'user' => $this->tokenStorage->getToken()->getUser(),
                                    'team' => $team,
                                    'strainId' => $strainId,
                                ])
                            ->orderBy('strain.autoName', 'ASC');
                    },
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
            ]);

            $form->add('strainPlasmids', CollectionType::class, [
                'entry_type' => StrainPlasmidType::class,
                'entry_options' => [
                    'parent_data' => $team,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $strain = $event->getData();
                $strainId = $strain->getId() ? $strain->getId() : null;

                // If it's a new strain, the default team is the FavoriteTeam of the user
                // else, we retrieve the team from the type
                if (null === $strain->getId()) {
                    $team = $this->tokenStorage->getToken()->getUser()->getFavoriteTeam();
                } else {
                    $team = $strain->getTeam();
                }

                $formModifier($event->getForm(), $team, $strainId);
            }
        );

        $builder->get('team')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                $team = $event->getForm()->getData();
                $strain = $event->getForm()->getParent()->getData();
                $strainId = $strain->getId() ? $strain->getId() : 0;

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback functions!
                $formModifier($event->getForm()->getParent(), $team, $strainId);
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
