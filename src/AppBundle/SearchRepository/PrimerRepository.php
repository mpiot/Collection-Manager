<?php

namespace AppBundle\SearchRepository;

use AppBundle\Entity\User;
use FOS\ElasticaBundle\Repository;

class PrimerRepository extends Repository
{
    public function search($search, $userTeams, User $author = null)
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

            // Search in sequence
            $sequenceQuery = new  \Elastica\Query\Match();
            $sequenceQuery->setFieldQuery('sequence', $search);
            $boolQuery->addShould($sequenceQuery);
        }

        $teamsQuery = new \Elastica\Query\Terms();
        $teamsQuery->setTerms('team', $userTeams);
        $boolQuery->addFilter($teamsQuery);

        if (null !== $author && '' !== $author) {
            $authorQuery = new \Elastica\Query\Term();
            $authorQuery->setTerm('author', $author->getId());
            $boolQuery->addFilter($authorQuery);
        }

        // build $query with Elastica objects
        return $this->find($boolQuery);
    }
}
