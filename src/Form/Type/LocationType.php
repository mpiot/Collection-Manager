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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param array
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parent', EntityType::class, [
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
                'required' => false,
                'placeholder' => '-- select a location --',
            ])
            ->add('name', TextType::class)
        ;

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $location = $event->getData();

                // If the user don't select a Parent, then we define the parent on Location
                // This is the root tree
                if (null === $location->getParent()) {
                    $parent = $this->entityManager->getRepository('App:Location')->findOneByName('Location');
                    $location->setParent($parent);
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Location',
        ]);
    }
}
