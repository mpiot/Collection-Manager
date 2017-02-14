<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;

/**
 * PlasmidRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PlasmidRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllForUser(User $user)
    {
        $query = $this->createQueryBuilder('plasmid')
            ->innerJoin('plasmid.team', 'team')
            ->innerJoin('team.members', 'members')
            ->where('members = :user')
                ->setParameter('user', $user)
            ->getQuery();

        return $query->getResult();
    }
}
