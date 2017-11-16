<?php

namespace AppBundle\SearchRepository;

use AppBundle\Entity\Box;
use AppBundle\Entity\User;
use FOS\ElasticaBundle\Repository;

class BoxRepository extends Repository
{
    public function searchByNameQuery($q, $p, $projectId, User $user)
    {
        $query = new \Elastica\Query();
        $boolQuery = new \Elastica\Query\BoolQuery();

        $projectSecureQuery = new \Elastica\Query\Terms();
        $projectSecureQuery->setTerms('project_id', $user->getProjectsId());
        $boolQuery->addFilter($projectSecureQuery);

        // Only search in the type box
        $typeQuery = new \Elastica\Query\Type();
        $typeQuery->setType('box');
        $boolQuery->addFilter($typeQuery);

        if (null !== $q) {
            $queryString = new \Elastica\Query\QueryString();
            $queryString->setFields(['name', 'autoName', 'project']);
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

        if (null !== $projectId) {
            $projectQuery = new \Elastica\Query\Term();
            $projectQuery->setTerm('project_id', $projectId);
            $boolQuery->addFilter($projectQuery);
        }

        $query
            ->setFrom(($p - 1) * Box::NUM_ITEMS)
            ->setSize(Box::NUM_ITEMS);

        // build $query with Elastica objects
        return $query;
    }
}
