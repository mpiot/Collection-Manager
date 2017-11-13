<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\ProductMovement;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;

class ProductMovementSubscriber implements EventSubscriber
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

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
        $entity = $args->getObject();

        // If the entity is not a ProductMovement, return
        if (!$entity instanceof ProductMovement) {
            return;
        }

        $this->calculateStock($entity);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        // If the entity is not a ProductMovement, return
        if (!$entity instanceof ProductMovement) {
            return;
        }

        $this->calculateStock($entity);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        // If the entity is not a ProductMovement, return
        if (!$entity instanceof ProductMovement) {
            return;
        }

        $this->calculateStock($entity);
    }

    private function calculateStock(ProductMovement $productMovement)
    {
        // Get the product
        $product = $productMovement->getProduct();
        $stock = 0;

        foreach ($product->getMovements() as $movement) {
            $stock += $movement->getMovement();
        }

        // Set the stock
        $product->setStock($stock);

        // Persist and flush it
        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }
}
