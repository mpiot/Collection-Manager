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
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BoxType extends AbstractType
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
                        ->setParameter('user', $this->tokenStorage->getToken()->getUser());
                },
                'data' => $this->tokenStorage->getToken()->getUser()->getFavoriteGroup(),
                'choice_label' => 'name',
            ])
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Name of the box',
                ],
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'Description about the box',
                ],
            ])
            ->add('freezer', TextType::class, [
                'help' => 'In which freezer is the box ?',
                'attr' => [
                    'placeholder' => 'Emile',
                ],
            ])
            ->add('location', TextType::class, [
                'help' => 'Where is the box in the freezer ?',
                'label' => 'Location in the freezer',
                'attr' => [
                    'placeholder' => '1st shelf on the top, 3rd rack on the left',
                ],
            ])
            ->add('colNumber', NumberType::class, [
                'label' => 'Number of columns',
                'attr' => [
                    'placeholder' => '10',
                ],
            ])
            ->add('rowNumber', NumberType::class, [
                'label' => 'Number of rows',
                'attr' => [
                    'placeholder' => '10',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Box',
        ]);
    }
}
