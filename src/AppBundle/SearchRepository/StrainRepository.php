<?php

namespace AppBundle\SearchRepository;

use FOS\ElasticaBundle\Repository;

class StrainRepository extends Repository
{
    public function search($search, $deleted = false, $country = null)
    {
        // Create a Bool query
        $boolQuery = new \Elastica\Query\BoolQuery();

        // Request need to match at least on one ShouldQuery
        $boolQuery->setMinimumNumberShouldMatch(1);

        $systematicNameQuery = new  \Elastica\Query\Match();
        $systematicNameQuery->setFieldQuery('systematicName', $search);
        $systematicNameQuery->setFieldFuzziness('systematicName', 'AUTO');
        $boolQuery->addShould($systematicNameQuery);

        $usualNameQuery = new  \Elastica\Query\Match();
        $usualNameQuery->setFieldQuery('usualName', $search);
        $usualNameQuery->setFieldFuzziness('usualName', 'AUTO');
        $boolQuery->addShould($usualNameQuery);

        // Bool to string
        $deleted = $deleted ? 'true' : 'false';

        $deletedQuery = new \Elastica\Query\Terms();
        $deletedQuery->setTerms('deleted', [$deleted]);
        $boolQuery->addMust($deletedQuery);

        if (null !== $country) {
            $countryQuery = new \Elastica\Query\Terms();
            // Elastica index all in lowercase, but it try to mach uppercase on lowercase and never work
            // To-do: create index and search analyzer
            $countryQuery->setTerms('country', [strtolower($country)]);
            $boolQuery->addMust($countryQuery);
        }

        // build $query with Elastica objects
        return $this->find($boolQuery);
    }
}
