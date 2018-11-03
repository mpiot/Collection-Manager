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

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\RouterInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;

class ProductEditType extends AbstractType
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
            ->add('quoteFile', VichFileType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_uri' => function (Product $product) {
                    return $this->router->generate('product_download_quote', ['id' => $product->getId(), 'slug' => $product->getSlug()]);
                },
                'download_label' => function (Product $product) {
                    if (null !== $product->getQuoteName()) {
                        return $product->getSlug().'-quote.'.pathinfo($product->getQuoteName())['extension'];
                    }

                    return null;
                },
            ])
            ->add('manualFile', VichFileType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_uri' => function (Product $product) {
                    return $this->router->generate('product_download_manual', ['id' => $product->getId(), 'slug' => $product->getSlug()]);
                },
                'download_label' => function (Product $product) {
                    if (null !== $product->getManualName()) {
                        return $product->getSlug().'-manual.'.pathinfo($product->getManualName())['extension'];
                    }

                    return null;
                },
            ])
        ;
    }

    public function getParent()
    {
        return ProductType::class;
    }
}
