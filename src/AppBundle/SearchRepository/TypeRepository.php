<?php

namespace AppBundle\SearchRepository;

use AppBundle\Entity\Type;
use FOS\ElasticaBundle\Repository;

class TypeRepository extends Repository
{
    public function searchByNameQuery($q, $p)
    {
        $query = new \Elastica\Query();

        if (null !== $q) {
            $queryString = new \Elastica\Query\QueryString();
            $queryString->setFields(['name']);
            $queryString->setDefaultOperator('AND');
            $queryString->setQuery($q);

            $query->setQuery($queryString);
        } else {
            $matchAllQuery = new \Elastica\Query\MatchAll();

            $query->setQuery($matchAllQuery);
            $query->setSort(['name_raw' => 'asc']);
        }

        $query
            ->setFrom(($p - 1) * Type::NUM_ITEMS)
            ->setSize(Type::NUM_ITEMS);

        // build $query with Elastica objects
        return $query;
    }
}
