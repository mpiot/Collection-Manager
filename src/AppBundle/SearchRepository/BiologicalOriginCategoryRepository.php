<?php

namespace AppBundle\SearchRepository;

use AppBundle\Entity\BiologicalOriginCategory;
use FOS\ElasticaBundle\Repository;

class BiologicalOriginCategoryRepository extends Repository
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
            ->setFrom(($p - 1) * BiologicalOriginCategory::NUM_ITEMS)
            ->setSize(BiologicalOriginCategory::NUM_ITEMS);

        // build $query with Elastica objects
        return $query;
    }
}
