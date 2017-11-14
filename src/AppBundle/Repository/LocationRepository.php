<?php

namespace AppBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class LocationRepository extends NestedTreeRepository
{
    public function findAllOrganized()
    {
        $query = $this->createQueryBuilder('location')
            ->orderBy('location.root, location.lft', 'ASC')
            ->getQuery();

        return $query->getResult();
    }
}
