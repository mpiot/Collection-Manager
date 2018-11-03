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
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\Count;

class AdvancedSearchType extends AbstractType
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
            ->add('search', SearchType::class, [
                'required' => false,
                'attr' => [
                    'autocomplete' => 'off',
                ],
            ])
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Gmo' => 'gmo',
                    'Wild' => 'wild',
                    'Plasmid' => 'plasmid',
                    'Primer' => 'primer',
                    'Product' => 'product',
                    'Equipment' => 'equipment',
                ],
                'expanded' => true,
                'multiple' => true,
                'data' => ['gmo', 'wild', 'plasmid', 'primer', 'product', 'equipment'],
                'constraints' => [
                    new Count(['min' => 1, 'minMessage' => 'Select at least one element.']),
                ],
            ])
            ->add('country', CountryType::class, [
                'placeholder' => 'All countries',
                'required' => false,
            ])
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
                'placeholder' => 'All groups',
                'required' => false,
            ])
            ->add('author', EntityType::class, [
                'class' => 'App\Entity\User',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->leftJoin('user.groups', 'g')
                        ->where('g IN (:groups)')
                            ->setParameter('groups', $this->tokenStorage->getToken()->getUser()->getGroups())
                        ->orderBy('user.firstName', 'ASC')
                        ->addOrderBy('user.lastName', 'ASC');
                },
                'choice_label' => 'fullName',
                'placeholder' => 'All users',
                'required' => false,
            ])
        ;
    }
}
