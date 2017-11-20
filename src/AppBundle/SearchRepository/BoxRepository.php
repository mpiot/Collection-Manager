<?php

namespace AppBundle\SearchRepository;

use AppBundle\Entity\Box;
use AppBundle\Entity\User;
use FOS\ElasticaBundle\Repository;

class BoxRepository extends Repository
{
    public function searchByNameQuery($q, $p, $groupId, User $user)
    {
        $query = new \Elastica\Query();
        $boolQuery = new \Elastica\Query\BoolQuery();

        $groupSecureQuery = new \Elastica\Query\Terms();
        $groupSecureQuery->setTerms('group_id', $user->getGroupsId());
        $boolQuery->addFilter($groupSecureQuery);

        // Only search in the type box
        $typeQuery = new \Elastica\Query\Type();
        $typeQuery->setType('box');
        $boolQuery->addFilter($typeQuery);

        if (null !== $q) {
            $queryString = new \Elastica\Query\QueryString();
            $queryString->setFields(['name', 'autoName']);
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

        if (null !== $groupId) {
            $groupQuery = new \Elastica\Query\Term();
            $groupQuery->setTerm('group_id', $groupId);
            $boolQuery->addFilter($groupQuery);
        }

        $query
            ->setFrom(($p - 1) * Box::NUM_ITEMS)
            ->setSize(Box::NUM_ITEMS);

        // build $query with Elastica objects
        return $query;
    }
}
