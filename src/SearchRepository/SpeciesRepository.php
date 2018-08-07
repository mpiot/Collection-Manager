<?php

namespace App\SearchRepository;

use App\Entity\Species;
use FOS\ElasticaBundle\Repository;

class SpeciesRepository extends Repository
{
    public function searchByScientificNameQuery($q, $p)
    {
        $query = new \Elastica\Query();
        $boolQuery = new \Elastica\Query\BoolQuery();
        $query->setQuery($boolQuery);

        // Only search in the type type
        $typeQuery = new \Elastica\Query\Type();
        $typeQuery->setType('species');
        $boolQuery->addFilter($typeQuery);

        if (null !== $q) {
            $queryString = new \Elastica\Query\QueryString();
            $queryString->setFields(['name']);
            $queryString->setDefaultOperator('AND');
            $queryString->setQuery($q);

            $boolQuery->addMust($queryString);
        } else {
            $matchAllQuery = new \Elastica\Query\MatchAll();

            $boolQuery->addMust($matchAllQuery);
            $query->setSort(['name_raw' => 'asc']);
        }

        $query
            ->setFrom(($p - 1) * Species::NUM_ITEMS)
            ->setSize(Species::NUM_ITEMS);

        // build $query with Elastica objects
        return $query;
    }
}
