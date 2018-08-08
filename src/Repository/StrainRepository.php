<?php

namespace App\Repository;

use App\Entity\Strain;

/**
 * Strain Repository.
 */
class StrainRepository extends \Doctrine\ORM\EntityRepository
{
    public function findOneById($id)
    {
        $query = $this->createQueryBuilder('strain')
            ->leftJoin('strain.strainPlasmids', 'strainPlasmids')
                ->addSelect('strainPlasmids')
            ->leftJoin('strainPlasmids.plasmid', 'plasmid')
                ->addSelect('plasmid')
            ->leftJoin('strain.species', 'species')
                ->addSelect('species')
            ->leftJoin('species.genus', 'genus')
                ->addSelect('genus')
            ->leftJoin('strain.tubes', 'tubes')
                ->addSelect('tubes')
            ->leftJoin('tubes.box', 'boxes')
                ->addSelect('boxes')
            ->where('strain.id = :id')
                ->setParameter('id', $id)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findParents(Strain $strain)
    {
        $query = $this->createQueryBuilder('strain')
            ->leftJoin('strain.parents', 'parents')
                ->addSelect('parents')
            ->where('strain = :strain')
            ->setParameter('strain', $strain)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findChildren(Strain $strain)
    {
        $query = $this->createQueryBuilder('strain')
            ->leftJoin('strain.children', 'children')
            ->addSelect('children')
            ->where('strain = :strain')
            ->setParameter('strain', $strain)
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
