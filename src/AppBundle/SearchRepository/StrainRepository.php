<?php

namespace AppBundle\SearchRepository;

use AppBundle\Entity\Strain;
use AppBundle\Entity\User;
use FOS\ElasticaBundle\Repository;

class StrainRepository extends Repository
{
    public function searchByNameQuery($q, $p = null, $teamId, User $user)
    {
        $query = new \Elastica\Query();
        $boolQuery = new \Elastica\Query\BoolQuery();

        $teamSecureQuery = new \Elastica\Query\Terms();
        $teamSecureQuery->setTerms('team_id', $user->getTeamsId());
        $boolQuery->addFilter($teamSecureQuery);

        // Only search in the type strain
        $typeQuery = new \Elastica\Query\Type();
        $typeQuery->setType('strain');
        $boolQuery->addFilter($typeQuery);

        if (null !== $q) {
            $queryString = new \Elastica\Query\QueryString();
            $queryString->setFields(['name', 'autoName']);
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

        if (null !== $teamId) {
            $teamQuery = new \Elastica\Query\Term();
            $teamQuery->setTerm('team_id', $teamId);
            $boolQuery->addFilter($teamQuery);
        }

        if (null !== $p) {
            $query
                ->setFrom(($p - 1) * Strain::NUM_ITEMS)
                ->setSize(Strain::NUM_ITEMS);
        }

        // build $query with Elastica objects
        return $query;
    }
}
