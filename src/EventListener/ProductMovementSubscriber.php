<?php

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
