<?php

namespace AppBundle\SearchRepository;

use FOS\ElasticaBundle\Repository;

class StrainRepository extends Repository
{
    public function search($search, $userProjects, $deleted = false, $country = null, $project = null, $type = null)
    {
        // Do a BoolQuery
        $boolQuery = new \Elastica\Query\BoolQuery();

        // If the $search is not null add keyword in the search
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

        $projectsQuery = new \Elastica\Query\Terms();
        $projectsQuery->setTerms('projects', $userProjects);
        $boolQuery->addFilter($projectsQuery);

        if (null !== $country) {
            $countryQuery = new \Elastica\Query\Term();
            // We can't use an analyzer on a term, then we need to lower it here.
            $countryQuery->setTerm('country', strtolower($country));
            $boolQuery->addFilter($countryQuery);
        }

        if (null !== $project) {
            $projectQuery = new \Elastica\Query\Term();
            $projectQuery->setTerm('projects', $project->getId());
            $boolQuery->addFilter($projectQuery);
        }

        if (null !== $type) {
            $typeQuery = new \Elastica\Query\Term();
            $typeQuery->setTerm('type', $type->getId());
            $boolQuery->addFilter($typeQuery);
        }

        // build $query with Elastica objects
        return $this->find($boolQuery);
    }
}
