<?php

namespace AppBundle\SearchRepository;

use AppBundle\Entity\Primer;
use AppBundle\Entity\User;
use FOS\ElasticaBundle\Repository;

class PrimerRepository extends Repository
{
    public function searchByNameQuery($q, $p, $groupId, User $user)
    {
        $query = new \Elastica\Query();

        $groupSecureQuery = new \Elastica\Query\Terms();
        $groupSecureQuery->setTerms('group_id', $user->getGroupsId());

        $boolQuery = new \Elastica\Query\BoolQuery();
        $boolQuery->addFilter($groupSecureQuery);

        // Only search in the type primer
        $typeQuery = new \Elastica\Query\Type();
        $typeQuery->setType('primer');
        $boolQuery->addFilter($typeQuery);

        if (null !== $q) {
            $queryString = new \Elastica\Query\QueryString();
            $queryString->setFields(['name', 'autoName', 'sequence']);
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

        $groupQuery = new \Elastica\Query\Term();
        $groupQuery->setTerm('group_id', $groupId);
        $boolQuery->addFilter($groupQuery);

        $query
            ->setFrom(($p - 1) * Primer::NUM_ITEMS)
            ->setSize(Primer::NUM_ITEMS);

        // build $query with Elastica objects
        return $query;
    }
}
