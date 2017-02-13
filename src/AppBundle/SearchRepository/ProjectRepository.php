<?php

namespace AppBundle\SearchRepository;

use AppBundle\Entity\Project;
use AppBundle\Entity\User;
use FOS\ElasticaBundle\Repository;

class ProjectRepository extends Repository
{
    public function searchByNameQuery($q, $p, User $user)
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

        $memberFilter = new \Elastica\Query\Term();
        $memberFilter->setTerm('members', $user->getId());

        $query->setPostFilter($memberFilter);

        $query
            ->setFrom(($p - 1) * Project::NUM_ITEMS)
            ->setSize(Project::NUM_ITEMS);

        // build $query with Elastica objects
        return $query;
    }
}
