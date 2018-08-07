<?php

namespace App\SearchRepository;

use App\Entity\User;
use FOS\ElasticaBundle\Repository;

class UserRepository extends Repository
{
    public function searchByNameQuery($q, $p)
    {
        $boolQuery = new \Elastica\Query\BoolQuery();

        $query = new \Elastica\Query();
        $query->setQuery($boolQuery);

        // Only search in the type seller
        $typeQuery = new \Elastica\Query\Type();
        $typeQuery->setType('user');
        $boolQuery->addFilter($typeQuery);

        if (null !== $q) {
            $queryString = new \Elastica\Query\QueryString();
            $queryString->setFields(['fullName', 'email']);
            $queryString->setDefaultOperator('AND');
            $queryString->setQuery($q);

            $boolQuery->addMust($queryString);
        } else {
            $matchAllQuery = new \Elastica\Query\MatchAll();

            $boolQuery->addMust($matchAllQuery);
            $query->setSort(['fullName_raw' => 'asc']);
        }

        $query
            ->setFrom(($p - 1) * User::NUM_ITEMS)
            ->setSize(User::NUM_ITEMS);

        // build $query with Elastica objects
        return $query;
    }
}
