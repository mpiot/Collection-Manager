<?php

namespace AppBundle\SearchRepository;

use FOS\ElasticaBundle\Repository;

class TypeRepository extends Repository
{
    public function findByName($q, $p, $hpp)
    {
        if (null !== $q) {
            $query = new \Elastica\Query\QueryString();
            $query->setFields(['name']);
            $query->setDefaultOperator('AND');
            $query->setQuery($q);
        } else {
            $matchAllQuery = new \Elastica\Query\MatchAll();

            $query = new \Elastica\Query();
            $query->setQuery($matchAllQuery);
            $query->setSort(['name_raw' => 'asc']);
        }

        // build $query with Elastica objects
        return $this->findPaginated($query)
            ->setMaxPerPage($hpp)
            ->setCurrentPage($p);
    }
}
