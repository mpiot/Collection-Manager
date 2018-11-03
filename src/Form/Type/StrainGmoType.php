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
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('genotype', TextareaType::class, [
                'required' => false,
            ])
        ;

        $formModifier = function (FormInterface $form, $group = null, $strainId = null) {
            $form->add('parents', CollectionType::class, [
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => 'App\Entity\Strain',
                    'choice_label' => 'fullName',
                    'placeholder' => '-- select a parent --',
                    'query_builder' => function (EntityRepository $er) use ($group, $strainId) {
                        $query = $er->createQueryBuilder('strain')
                            ->leftJoin('strain.group', 'g')
                            ->leftJoin('g.members', 'members')
                            ->where('members = :user')
                            ->andWhere('g = :group')
                            ->setParameters([
                                'user' => $this->tokenStorage->getToken()->getUser(),
                                'group' => $group,
                            ]);

                        if (null !== $strainId) {
                            $query
                                ->andWhere('strain.id <> :strainId')
                                ->setParameter('strainId', $strainId);
                        }

                        $query->orderBy('strain.autoName', 'ASC');

                        return $query;
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
                    'parent_data' => $group,
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

                // If it's a new strain, the default group is the FavoriteGroup of the user
                // else, we retrieve the group from the type
                if (null === $strain->getId()) {
                    $group = $this->tokenStorage->getToken()->getUser()->getFavoriteGroup();
                } else {
                    $group = $strain->getGroup();
                }

                $formModifier($event->getForm(), $group, $strainId);
            }
        );

        $builder->get('group')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                $group = $event->getForm()->getData();
                $strain = $event->getForm()->getParent()->getData();
                $strainId = $strain->getId() ? $strain->getId() : 0;

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback functions!
                $formModifier($event->getForm()->getParent(), $group, $strainId);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Strain',
        ]);
    }

    public function getParent()
    {
        return StrainType::class;
    }
}
