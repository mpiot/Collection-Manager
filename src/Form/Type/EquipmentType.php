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

use App\Entity\Location;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EquipmentType extends AbstractType
{
    private $tokenStorage;

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
                        ->setParameter('user', $this->tokenStorage->getToken()->getUser())
                        ->orderBy('g.name', 'ASC');
                },
                'choice_label' => 'name',
                'data' => $this->tokenStorage->getToken()->getUser()->getFavoriteGroup(),
            ])
            ->add('name')
            ->add('description')
            ->add('serialNumber')
            ->add('purchaseDate', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('brand', EntityType::class, [
                'class' => 'App\Entity\Brand',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('brand')
                        ->orderBy('brand.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => '-- select a brand --',
            ])
            ->add('model')
            ->add('seller', EntityType::class, [
                'class' => 'App\Entity\Seller',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('seller')
                        ->orderBy('seller.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => '-- select a seller --',
            ])
            ->add('inventoryNumber')
            ->add('location', EntityType::class, [
                'class' => 'App\Entity\Location',
                'query_builder' => function (LocationRepository $er) {
                    return $er->createQueryBuilder('location')
                        ->leftJoin('location.root', 'root')
                        ->addSelect('root')
                        ->orderBy('location.lft', 'ASC')
                        ->where('root.name = :location')
                        ->andWhere('location.lvl != 0')
                        ->setParameter('location', 'Location');
                },
                'choice_label' => function (Location $location) {
                    return str_repeat('-', ($location->getLevel() - 1)).$location->getName();
                },
                'placeholder' => '-- select a location --',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Equipment',
        ]);
    }
}
