<?php

namespace AppBundle\SearchRepository;

use AppBundle\Entity\Seller;
use FOS\ElasticaBundle\Repository;

class SellerRepository extends Repository
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
            ->setFrom(($p - 1) * Seller::NUM_ITEMS)
            ->setSize(Seller::NUM_ITEMS);

        // build $query with Elastica objects
        return $query;
    }
}
