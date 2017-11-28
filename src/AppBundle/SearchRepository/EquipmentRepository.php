<?php

namespace AppBundle\SearchRepository;

use AppBundle\Entity\Equipment;
use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use FOS\ElasticaBundle\Repository;

class EquipmentRepository extends Repository
{
    public function searchByNameQuery($q, $p, $groupId, User $user)
    {
        $query = new \Elastica\Query();

        $groupSecureQuery = new \Elastica\Query\Terms();
        $groupSecureQuery->setTerms('group_id', $user->getGroupsId());

        $boolQuery = new \Elastica\Query\BoolQuery();
        $boolQuery->addFilter($groupSecureQuery);

        // Only search in the type product
        $typeQuery = new \Elastica\Query\Type();
        $typeQuery->setType('equipment');
        $boolQuery->addFilter($typeQuery);

        if (null !== $q) {
            $queryString = new \Elastica\Query\QueryString();
            $queryString->setFields(['name']);
            $queryString->setDefaultOperator('AND');
            $queryString->setQuery($q);

            $boolQuery->addMust($queryString);
            $query->setQuery($boolQuery);
        } else {
            $matchAllQuery = new \Elastica\Query\MatchAll();

            $boolQuery->addMust($matchAllQuery);
            $query->setQuery($boolQuery);
            $query->setSort(['name_raw' => 'asc']);
        }

        $groupQuery = new \Elastica\Query\Term();
        $groupQuery->setTerm('group_id', $groupId);
        $boolQuery->addFilter($groupQuery);

        $query
            ->setFrom(($p - 1) * Equipment::NUM_ITEMS)
            ->setSize(Equipment::NUM_ITEMS);

        // build $query with Elastica objects
        return $query;
    }
}
