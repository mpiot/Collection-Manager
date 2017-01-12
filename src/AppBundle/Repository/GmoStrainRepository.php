<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Strain;
use AppBundle\Entity\User;

/**
 * GMORepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class GmoStrainRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllName()
    {
        $query = $this->createQueryBuilder('gmo')
            ->select('gmo.name')
            ->orderBy('gmo.name', 'ASC')
            ->distinct()
            ->getQuery();

        return $query->getArrayResult();
    }

    public function findAllForUser(User $user, $limit = 10)
    {
        $query = $this->createQueryBuilder('strain')
            ->leftJoin('strain.tubes', 'tubes')
            ->leftJoin('tubes.box', 'boxes')
            ->leftJoin('boxes.project', 'projects')
            ->leftJoin('projects.members', 'members')
            ->leftJoin('strain.author', 'author')
                ->addSelect('author')
            ->leftJoin('projects.administrators', 'administrators')
            ->where('members = :user')
            ->orWhere('author = :user')
            ->orWhere('administrators = :user')
                ->setParameter('user', $user)
            ->distinct(true)
            ->orderBy('strain.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery();

        return $query->getResult();
    }

    public function findOneWithAll($strain)
    {
        $query = $this->createQueryBuilder('gmo')
            ->leftJoin('gmo.strainPlasmids', 'strainPlasmids')
                ->addSelect('strainPlasmids')
            ->leftJoin('strainPlasmids.plasmid', 'plasmid')
                ->addSelect('plasmid')
            ->leftJoin('gmo.species', 'species')
                ->addSelect('species')
            ->leftJoin('species.genus', 'genus')
                ->addSelect('genus')
            ->leftJoin('gmo.tubes', 'tubes')
                ->addSelect('tubes')
            ->leftJoin('tubes.box', 'boxes')
                ->addSelect('boxes')
            ->leftJoin('boxes.project', 'projects')
                ->addSelect('projects')
            ->where('gmo = :strain')
                ->setParameter('strain', $strain)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findParents(Strain $strain)
    {
        $query = $this->createQueryBuilder('gmo')
            ->leftJoin('gmo.parents', 'parents')
                ->addSelect('parents')
            ->where('gmo = :strain')
            ->setParameter('strain', $strain)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findChildren(Strain $strain)
    {
        $query = $this->createQueryBuilder('gmo')
            ->leftJoin('gmo.children', 'children')
            ->addSelect('children')
            ->where('gmo = :strain')
            ->setParameter('strain', $strain)
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
