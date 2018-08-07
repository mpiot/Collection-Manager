<?php

namespace App\SearchRepository;

use App\Entity\Seller;
use FOS\ElasticaBundle\Repository;

class SellerRepository extends Repository
{
    public function searchByNameQuery($q, $p)
    {
        $boolQuery = new \Elastica\Query\BoolQuery();

        $query = new \Elastica\Query();
        $query->setQuery($boolQuery);

        // Only search in the type seller
        $typeQuery = new \Elastica\Query\Type();
        $typeQuery->setType('seller');
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
            ->setFrom(($p - 1) * Seller::NUM_ITEMS)
            ->setSize(Seller::NUM_ITEMS);

        // build $query with Elastica objects
        return $query;
    }
}
