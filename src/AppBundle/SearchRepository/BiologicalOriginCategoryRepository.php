<?php

namespace AppBundle\SearchRepository;

use AppBundle\Entity\BiologicalOriginCategory;
use AppBundle\Entity\User;
use FOS\ElasticaBundle\Repository;

class BiologicalOriginCategoryRepository extends Repository
{
    public function searchByNameQuery($q, $p, User $user)
    {
        $teamSecureQuery = new \Elastica\Query\Term();
        $teamSecureQuery->setTerm('team_id', $user->getTeamsId()[0]);

        $query = new \Elastica\Query();
        $boolQuery = new \Elastica\Query\BoolQuery();
        $boolQuery->addFilter($teamSecureQuery);

        if (null !== $q) {
            $queryString = new \Elastica\Query\QueryString();
            $queryString->setFields(['name']);
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

        $query
            ->setFrom(($p - 1) * BiologicalOriginCategory::NUM_ITEMS)
            ->setSize(BiologicalOriginCategory::NUM_ITEMS);

        // build $query with Elastica objects
        return $query;
    }
}
