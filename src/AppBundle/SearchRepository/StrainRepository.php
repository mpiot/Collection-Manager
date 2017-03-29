<?php

namespace AppBundle\SearchRepository;

use AppBundle\Entity\Strain;
use AppBundle\Entity\User;
use FOS\ElasticaBundle\Repository;

class StrainRepository extends Repository
{
    public function searchByNameQuery($q, $p = null, $projectId, User $user)
    {
        $projectsSecureQuery = new \Elastica\Query\Terms();
        $projectsSecureQuery->setTerms('project_id', $user->getProjectsId());

        $query = new \Elastica\Query();

        $boolQuery = new \Elastica\Query\BoolQuery();
        $boolQuery->addFilter($projectsSecureQuery);

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

        if (null !== $projectId) {
            $projectQuery = new \Elastica\Query\Term();
            $projectQuery->setTerm('project_id', $projectId);
            $boolQuery->addFilter($projectQuery);
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
