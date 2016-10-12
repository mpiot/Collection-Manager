<?php

namespace AppBundle\SearchRepository;

use FOS\ElasticaBundle\Repository;

class StrainRepository extends Repository
{
    public function search($search, $userTeams, $deleted = false, $country = null)
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
        $deletedQuery = new \Elastica\Query\Term();
        $deletedQuery->setTerm('deleted', $deleted);
        $boolQuery->addFilter($deletedQuery);

        $teamQuery = new \Elastica\Query\Terms();
        $teamQuery->setTerms('authorizedTeams', $userTeams);
        $boolQuery->addFilter($teamQuery);

        if (null !== $country) {
            $countryQuery = new \Elastica\Query\Term();
            // We can't use an analyzer on a term, then we need to lower it here.
            $countryQuery->setTerm('country', strtolower($country));
            $boolQuery->addFilter($countryQuery);
        }

        // build $query with Elastica objects
        return $this->find($boolQuery);
    }
}
