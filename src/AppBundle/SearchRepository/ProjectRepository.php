<?php

namespace AppBundle\SearchRepository;

use AppBundle\Entity\Project;
use AppBundle\Entity\User;
use FOS\ElasticaBundle\Repository;

class ProjectRepository extends Repository
{
    public function searchByNameQuery($q, $p, User $user)
    {
        $teamFilter = new \Elastica\Query\Term();
        $teamFilter->setTerm('team_id', $user->getTeamsId()[0]);

        $memberFilter = new \Elastica\Query\Term();
        $memberFilter->setTerm('members', $user->getId());

        $query = new \Elastica\Query();
        $boolQuery = new \Elastica\Query\BoolQuery();

        $boolQuery->setMinimumNumberShouldMatch(1);
        $boolQuery->addShould($teamFilter);
        $boolQuery->addShould($memberFilter);

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
            ->setFrom(($p - 1) * Project::NUM_ITEMS)
            ->setSize(Project::NUM_ITEMS);

        // build $query with Elastica objects
        return $query;
    }
}
