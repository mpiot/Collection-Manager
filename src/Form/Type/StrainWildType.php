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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StrainWildType extends AbstractType
{
    /**
     * @param array
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('biologicalOrigin', TextType::class, [
                'help' => 'Where did you find it ?',
                'attr' => [
                    'placeholder' => 'Galeria melonella, Insect',
                ],
            ])
            ->add('source', TextType::class, [
                'help' => 'Who give it to you ?',
                'attr' => [
                    'placeholder' => 'CBS, ...',
                ],
                'required' => false,
            ])
            ->add('address', TextType::class, [
                'help' => 'The address with this format: Address, Zip code City, Country',
                'attr' => [
                    'placeholder' => 'Pyramides, 75001 Paris, France',
                ],
                'required' => false,
            ])
            ->add('country', CountryType::class, [
                'placeholder' => '-- Choose a country --',
                'required' => false,
            ])
            ->add('latitude', NumberType::class, [
                'scale' => 6,
                'attr' => [
                    'placeholder' => 48.866667,
                ],
                'required' => false,
            ])
            ->add('longitude', NumberType::class, [
                'scale' => 6,
                'attr' => [
                    'placeholder' => 2.333333,
                ],
                'required' => false,
            ])
        ;
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
