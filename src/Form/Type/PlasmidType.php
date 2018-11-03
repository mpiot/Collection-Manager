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

use App\Entity\Plasmid;
use App\Entity\Primer;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;

class PlasmidType extends AbstractType
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
            ->add('genBankFile', VichFileType::class, [
                'required' => false,
                'allow_delete' => false,
                'download_uri' => false,
                'download_label' => false,
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

            $form->add('primers', CollectionType::class, [
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => 'App\Entity\Primer',
                    'placeholder' => '-- select a primer --',
                    'query_builder' => function (EntityRepository $er) use ($group) {
                        return $er->createQueryBuilder('primer')
                            ->leftJoin('primer.group', 'g')
                            ->where('g = :group')
                            ->setParameter('group', $group)
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

                // If it's a new plasmid, the default group is the FavoriteGroup of the user
                // else, we retrieve the group from the type
                if (null === $plasmid->getId()) {
                    $group = $this->tokenStorage->getToken()->getUser()->getFavoriteGroup();
                } else {
                    // We set the group
                    $group = $plasmid->getGroup();

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
            'data_class' => 'App\Entity\Plasmid',
        ]);
    }
}
