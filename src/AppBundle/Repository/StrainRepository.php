<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Strain;
use AppBundle\Entity\User;

/**
 * Strain Repository.
 */
class StrainRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllName(User $user)
    {
        $query = $this->createQueryBuilder('strain')
            ->leftJoin('strain.tubes', 'tubes')
            ->leftJoin('tubes.project', 'project')
            ->leftJoin('project.members', 'members')
            ->where('members = :user')
            ->setParameter('user', $user)
            ->select('strain.name')
            ->orderBy('strain.name', 'ASC')
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

    public function findOneBySlug($id)
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
            ->leftJoin('boxes.project', 'projects')
                ->addSelect('projects')
            ->leftJoin('strain.parents', 'parents')
                ->addSelect('parents')
            ->leftJoin('parents.tubes', 'parenttubes')
                ->addSelect('parenttubes')
            ->leftJoin('parenttubes.project', 'parentproject')
                ->addSelect('parentproject')
            ->leftJoin('parentproject.members', 'parentmembers')
                ->addSelect('parentmembers')
            ->leftJoin('strain.children', 'children')
                ->addSelect('children')
            ->leftJoin('children.tubes', 'childrentubes')
                ->addSelect('childrentubes')
            ->leftJoin('childrentubes.project', 'childrenproject')
                ->addSelect('childrenproject')
            ->leftJoin('childrenproject.members', 'childremembers')
                ->addSelect('childremembers')
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
