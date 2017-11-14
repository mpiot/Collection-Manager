<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Primer;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PlasmidType extends AbstractType
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
            ->add('addGenBankFile', ChoiceType::class, [
                'choices' => [
                    'No' => 0,
                    'Yes' => 1,
                ],
                'multiple' => false,
                'expanded' => true,
                'label' => 'Send a GenBank file ?',
            ])
            ->add('genBankFile', GenBankFileType::class, [
                'required' => false,
            ])
        ;

        $formModifier = function (FormInterface $form, $team = null) {
            $form->add('primers', CollectionType::class, [
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => 'AppBundle\Entity\Primer',
                    'placeholder' => '-- select a primer --',
                    'query_builder' => function (EntityRepository $er) use ($team) {
                        return $er->createQueryBuilder('primer')
                            ->leftJoin('primer.team', 'team')
                            ->where('team = :team')
                            ->setParameter('team', $team)
                            ->orderBy('primer.name', 'ASC');
                    },
                    'choice_label' => function (Primer $primer) {
                        return $primer->getAutoName().' - '.$primer->getName();
                    },
                ],
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $plasmid = $event->getData();
                $form = $event->getForm();

                if (null !== $plasmid && null !== $plasmid->getGenBankFile()) {
                    if (null !== $plasmid->getGenBankFile()->getPath()) {
                        $plasmid->setAddGenBankFile(true);
                    }
                }

                // If it's a new plasmid, the default team is the FavoriteTeam of the user
                // else, we retrieve the team from the type
                if (null === $plasmid->getId()) {
                    $team = $this->tokenStorage->getToken()->getUser()->getFavoriteTeam();
                } else {
                    // We set the team
                    $team = $plasmid->getTeam();

                    //And, we remove the team field
                    $form->remove('team');
                }

                $formModifier($form, $team);
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
            'data_class' => 'AppBundle\Entity\Plasmid',
        ]);
    }
}
