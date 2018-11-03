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
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StrainPlasmidType extends AbstractType
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
            ->add('plasmid', EntityType::class, [
                'class' => 'App\Entity\Plasmid',
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('plasmid')
                        ->leftJoin('plasmid.group', 'g')
                        ->leftJoin('g.members', 'members')
                        ->where('members = :user')
                        ->andWhere('g = :group')
                            ->setParameters([
                                'user' => $this->tokenStorage->getToken()->getUser(),
                                'group' => $options['parent_data'],
                            ])
                        ->orderBy('plasmid.autoName', 'ASC')
                    ;
                },
                'choice_label' => function (Plasmid $plasmid) {
                    return $plasmid->getAutoName().' - '.$plasmid->getName();
                },
                'placeholder' => '-- select a plasmid --',
            ])
            ->add('state', ChoiceType::class, [
                'choices' => [
                    'Replicative' => 'replicative',
                    'Integrated' => 'integrated',
                    'Cured' => 'cured',
                ],
                'placeholder' => '-- select a state --',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\StrainPlasmid',
        ]);

        $resolver->setRequired(['parent_data']);
    }
}
