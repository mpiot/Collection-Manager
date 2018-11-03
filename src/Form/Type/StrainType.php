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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StrainType extends AbstractType
{
    protected $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('group', EntityType::class, [
                'class' => 'App\Entity\Group',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('g')
                        ->leftJoin('g.members', 'members')
                        ->where('members = :user')
                            ->setParameter('user', $this->tokenStorage->getToken()->getUser());
                },
                'data' => $this->tokenStorage->getToken()->getUser()->getFavoriteGroup(),
                'choice_label' => 'name',
            ])
            ->add('species', EntityType::class, [
                'class' => 'App\Entity\Species',
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
                'required' => false,
            ])
            ->add('name', TextType::class, [
                'help' => 'The name you want use to communicate about this strain.',
                'attr' => [
                    'autocomplete' => 'off',
                ],
            ])
            ->add('uniqueCode', TextType::class, [
                'help' => 'A unique code for this strain.',
                'required' => false,
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
            ])
            ->add('sequenced', CheckboxType::class, [
                'required' => false,
            ])
        ;

        $formModifier = function (FormInterface $form, $group) {
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
                $form = $event->getForm();
                $strain = $event->getData();

                // If it's a new strain, the default group is the FavoriteGroup of the user
                // else, we retrieve the group
                if (null === $strain->getId()) {
                    $group = $this->tokenStorage->getToken()->getUser()->getFavoriteGroup();
                } else {
                    $group = $strain->getGroup();

                    // And, remove the group field
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
}
