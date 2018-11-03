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

use App\Entity\Genus;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StrainSpeciesType extends AbstractType
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $species = $event->getData();

        // For the species field
        $genus = $species ? $species->getGenus() : null;
        $this->addSpeciesElements($form, $genus);
    }

    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        // If the user want select a species
        if (isset($data['genus'])) {
            $genus = $this->em->getRepository('App:Genus')->findOneById($data['genus']);
            $this->addSpeciesElements($form, $genus);
        }
    }

    protected function addSpeciesElements(FormInterface $form, Genus $genus = null)
    {
        $form->add('genus', EntityType::class, [
            'class' => 'App\Entity\Genus',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('genus')
                    ->orderBy('genus.name', 'ASC');
            },
            'choice_label' => 'name',
            'placeholder' => '-- select a genus --',
            'mapped' => false,
            'required' => true,
            'data' => $genus,
        ]);

        $form->add('species', EntityType::class, [
            'class' => 'App\Entity\Species',
            'query_builder' => function (EntityRepository $er) use ($genus) {
                return $er->createQueryBuilder('species')
                    ->where('species.genus = :genus')
                    ->setParameter('genus', $genus)
                    ->orderBy('species.name', 'ASC');
            },
            'placeholder' => '-- select a species --',
            'choice_label' => 'name',
            'label' => 'Species',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            //'data_class' => 'App\Entity\Species',
            'inherit_data' => true,
        ]);
    }
}
