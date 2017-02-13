<?php

namespace AppBundle\SearchRepository;

use AppBundle\Entity\Species;
use FOS\ElasticaBundle\Repository;

class SpeciesRepository extends Repository
{
    public function searchByScientificNameQuery($q, $p)
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
            ->setFrom(($p - 1) * Species::NUM_ITEMS)
            ->setSize(Species::NUM_ITEMS);

        // build $query with Elastica objects
        return $query;
    }
}
