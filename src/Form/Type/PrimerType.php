<?php

/*
 * Copyright 2016-2018 Mathieu Piot.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
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
     * @param array
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('group', EntityType::class, [
                'class' => 'App\Entity\Group',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('g')
                        ->leftJoin('g.members', 'members')
                        ->where('members = :user')
                            ->setParameter('user', $this->tokenStorage->getToken()->getUser())
                        ->orderBy('g.name', 'ASC');
                },
                'data' => $this->tokenStorage->getToken()->getUser()->getFavoriteGroup(),
            ])
            ->add('name', TextType::class, [
                'help' => 'The name you want to use.',
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

        $formModifier = function (FormInterface $form, $group = null) {
            $form->add('tubes', CollectionType::class, [
                'entry_type' => TubeType::class,
                'entry_options' => [
                    'parent_data' => $group,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                // Use add and remove properties in the entity
                'by_reference' => false,
                'required' => false,
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $primer = $event->getData();
                $form = $event->getForm();

                // If it's a new primer, the default group is the FavoriteGroup of the user
                // else, retrieve the group
                if (null === $primer->getId()) {
                    $group = $this->tokenStorage->getToken()->getUser()->getFavoriteGroup();
                } else {
                    // We set the group
                    $group = $primer->getGroup();

                    //And, we remove the group field
                    $form->remove('group');
                }

                $formModifier($form, $group);
            }
        );

        $builder->get('group')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                $group = $event->getForm()->getData();

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback functions!
                $formModifier($event->getForm()->getParent(), $group);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Primer',
        ]);
    }
}
