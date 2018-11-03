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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\RouterInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;

class PlasmidEditType extends AbstractType
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param array
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('group')
            ->add('genBankFile', VichFileType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_uri' => function (Plasmid $plasmid) {
                    return $this->router->generate('plasmid_download', ['id' => $plasmid->getId(), 'slug' => $plasmid->getSlug()]);
                },
                'download_label' => function (Plasmid $plasmid) {
                    if (null !== $plasmid->getGenBankName()) {
                        return $plasmid->getAutoName().'_'.$plasmid->getSlug().'.'.pathinfo($plasmid->getGenBankName())['extension'];
                    }

                    return null;
                },
            ])
        ;
    }

    public function getParent()
    {
        return PlasmidType::class;
    }
}
