<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;

/**
 * BoxRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BoxRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllAuthorizedForCurrentUserWithType(User $user)
    {
        $query = $this->createQueryBuilder('box')
            ->innerJoin('box.project', 'project')
            ->innerJoin('project.members', 'members')
            ->innerJoin('project.team', 'team')
            ->innerJoin('team.administrators', 'administrators')
            ->where('administrators = :user')
            ->orWhere('members = :user')
                ->setParameter('user', $user)
            ->orderBy('box.project', 'ASC')
            ->addOrderBy('box.boxLetter', 'ASC')
            ->getQuery();

        return $query->getResult();
    }

    public function findOneWithProjectTypeTubesStrains($box)
    {
        $query = $this->createQueryBuilder('box')
            ->innerJoin('box.project', 'project')
                ->addSelect('project')
            ->innerJoin('box.tubes', 'tubes')
                ->addSelect('tubes')
            ->innerJoin('tubes.gmoStrain', 'g')
                ->addSelect('g')
            ->innerJoin('tubes.wildStrain', 'w')
                ->addSelect('w')
            ->where('box = :box')
            ->setParameter('box', $box)
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
