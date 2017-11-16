<?php

namespace AppBundle\SearchRepository;

use AppBundle\Entity\Project;
use AppBundle\Entity\User;
use FOS\ElasticaBundle\Repository;

class ProjectRepository extends Repository
{
    public function searchByNameQuery($q, $p, User $user)
    {
        $memberSecureQuery = new \Elastica\Query\Term();
        $memberSecureQuery->setTerm('members_id', $user->getId());

        $teamsSecureQuery = new \Elastica\Query\Terms();
        $teamsSecureQuery->setTerms('team_id', $user->getAdministeredTeamsId());

        $query = new \Elastica\Query();
        $boolQuery = new \Elastica\Query\BoolQuery();

        // Only search in the type project
        $typeQuery = new \Elastica\Query\Type();
        $typeQuery->setType('project');
        $boolQuery->addFilter($typeQuery);

        $boolQuery->setMinimumShouldMatch(1);
        $boolQuery->addShould($memberSecureQuery);
        $boolQuery->addShould($teamsSecureQuery);

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
