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
        $systematicNameQuery->setFieldPrefixLength('systematicName', 0);
        $boolQuery->addShould($systematicNameQuery);

        $usualNameQuery = new  \Elastica\Query\Match();
        $usualNameQuery->setFieldQuery('usualName', $search);
        $usualNameQuery->setFieldFuzziness('usualName', 'AUTO');
        $usualNameQuery->setFieldPrefixLength('usualName', 0);
        $boolQuery->addShould($usualNameQuery);

        // Bool to string
        $deleted = $deleted ? 'true' : 'false';
        $deletedQuery = new \Elastica\Query\Terms();
        $deletedQuery->setTerms('deleted', [$deleted]);
        $boolQuery->addMust($deletedQuery);

        if (null !== $country) {
            $countryQuery = new \Elastica\Query\Terms();
            // We can't use an analyzer on a term, then we need to lower it here.
            $countryQuery->setTerms('country', [strtolower($country)]);
            $boolQuery->addMust($countryQuery);
        }

        // build $query with Elastica objects
        return $this->find($boolQuery);
    }
}
