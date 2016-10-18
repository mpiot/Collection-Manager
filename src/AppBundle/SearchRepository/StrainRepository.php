<?php

namespace AppBundle\SearchRepository;

use FOS\ElasticaBundle\Repository;

class StrainRepository extends Repository
{
    public function search($search, $userTeams, $deleted = false, $country = null, $project = null)
    {
        // Do a BoolQuery
        $boolQuery = new \Elastica\Query\BoolQuery();

        // If the $search is not null add keyword in th search
        if (null !== $search) {
            // Request need to match at least on one ShouldQuery
            $boolQuery->setMinimumNumberShouldMatch(1);

            // Search in systematicName
            $systematicNameQuery = new  \Elastica\Query\Match();
            $systematicNameQuery->setFieldQuery('systematicName', $search);
            $systematicNameQuery->setFieldFuzziness('systematicName', 'AUTO');
            $boolQuery->addShould($systematicNameQuery);

            // Search in usualName
            $usualNameQuery = new  \Elastica\Query\Match();
            $usualNameQuery->setFieldQuery('usualName', $search);
            $usualNameQuery->setFieldFuzziness('usualName', 'AUTO');
            $boolQuery->addShould($usualNameQuery);
        }

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

        if (null !== $project) {
            dump($project->getId());

            $projectQuery = new \Elastica\Query\Term() ;
            $projectQuery->setTerm('projects', $project->getId());
            $boolQuery->addFilter($projectQuery);
        }

        // build $query with Elastica objects
        return $this->find($boolQuery);
    }
}
