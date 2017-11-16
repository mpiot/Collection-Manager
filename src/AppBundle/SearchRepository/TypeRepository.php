<?php

namespace AppBundle\SearchRepository;

use AppBundle\Entity\Type;
use AppBundle\Entity\User;
use FOS\ElasticaBundle\Repository;

class TypeRepository extends Repository
{
    public function searchByNameQuery($q, $p, $teamId, User $user)
    {
        $query = new \Elastica\Query();

        $teamSecureQuery = new \Elastica\Query\Terms();
        $teamSecureQuery->setTerms('team_id', $user->getTeamsId());

        $boolQuery = new \Elastica\Query\BoolQuery();
        $boolQuery->addFilter($teamSecureQuery);

        // Only search in the type type
        $typeQuery = new \Elastica\Query\Type();
        $typeQuery->setType('type');
        $boolQuery->addFilter($typeQuery);

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

        $teamQuery = new \Elastica\Query\Term();
        $teamQuery->setTerm('team_id', $teamId);
        $boolQuery->addFilter($teamQuery);

        $query
            ->setFrom(($p - 1) * Type::NUM_ITEMS)
            ->setSize(Type::NUM_ITEMS);

        // build $query with Elastica objects
        return $query;
    }
}
