<?php

namespace AppBundle\SearchRepository;

use AppBundle\Entity\Brand;
use FOS\ElasticaBundle\Repository;

class BrandRepository extends Repository
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
            ->setFrom(($p - 1) * Brand::NUM_ITEMS)
            ->setSize(Brand::NUM_ITEMS);

        // build $query with Elastica objects
        return $query;
    }
}
