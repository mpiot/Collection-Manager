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

namespace App\EventListener;

use App\Entity\ProductMovement;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class ProductMovementSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            'postPersist',
            'postUpdate',
            'postRemove',
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->calculateStock($args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->calculateStock($args);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $this->calculateStock($args);
    }

    private function calculateStock($args)
    {
        // Check if the object is a ProductMovement
        $object = $args->getObject();
        // If the entity is not a ProductMovement, return
        if (!$object instanceof ProductMovement) {
            return;
        }

        // Get the product
        $product = $object->getProduct();
        $stock = 0;
        foreach ($product->getMovements() as $movement) {
            $stock += $movement->getMovement();
        }
        // Set the stock
        $product->setStock($stock);

        // Persist and flush it
        $entityManager = $args->getEntityManager();
        $entityManager->persist($product);
        $entityManager->flush();
    }
}
