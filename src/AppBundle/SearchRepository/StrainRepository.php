<?php

namespace AppBundle\SearchRepository;

use AppBundle\Entity\Project;
use AppBundle\Entity\Type;
use AppBundle\Entity\User;
use FOS\ElasticaBundle\Repository;

class StrainRepository extends Repository
{
    public function search($search, $userProjects, $deleted = false, $country = null, Project $project = null, Type $type = null, User $author = null)
    {
        // Do a BoolQuery
        $boolQuery = new \Elastica\Query\BoolQuery();

        // If the $search is not null add keyword in the search
        if (null !== $search) {
            // Request need to match at least on one ShouldQuery
            $boolQuery->setMinimumNumberShouldMatch(1);

            // Search in autoName
            $autoNameQuery = new  \Elastica\Query\Match();
            $autoNameQuery->setFieldQuery('autoName', $search);
            $autoNameQuery->setFieldFuzziness('autoName', 'AUTO');
            $boolQuery->addShould($autoNameQuery);

            // Search in name
            $nameQuery = new  \Elastica\Query\Match();
            $nameQuery->setFieldQuery('name', $search);
            $nameQuery->setFieldFuzziness('name', 'AUTO');
            $boolQuery->addShould($nameQuery);
        }

        // Bool to string
        $deleted = $deleted ? 'true' : 'false';
        $deletedQuery = new \Elastica\Query\Term();
        $deletedQuery->setTerm('deleted', $deleted);
        $boolQuery->addFilter($deletedQuery);

        $projectsQuery = new \Elastica\Query\Terms();
        $projectsQuery->setTerms('projects', $userProjects);
        $boolQuery->addFilter($projectsQuery);

        if (null !== $country && '' !== $country) {
            $countryQuery = new \Elastica\Query\Term();
            // We can't use an analyzer on a term, then we need to lower it here.
            $countryQuery->setTerm('country', strtolower($country));
            $boolQuery->addFilter($countryQuery);
        }

        if (null !== $project && '' !== $project) {
            $projectQuery = new \Elastica\Query\Term();
            $projectQuery->setTerm('projects', $project->getId());
            $boolQuery->addFilter($projectQuery);
        }

        if (null !== $type && '' !== $type) {
            $typeQuery = new \Elastica\Query\Term();
            $typeQuery->setTerm('type', $type->getId());
            $boolQuery->addFilter($typeQuery);
        }

        if (null !== $author && '' !== $author) {
            $authorQuery = new \Elastica\Query\Term();
            $authorQuery->setTerm('author', $author->getId());
            $boolQuery->addFilter($authorQuery);
        }

        // build $query with Elastica objects
        return $this->find($boolQuery);
    }
}
