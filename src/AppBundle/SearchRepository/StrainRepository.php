<?php

namespace AppBundle\SearchRepository;

use FOS\ElasticaBundle\Repository;

class StrainRepository extends Repository
{
    public function findByNames($search)
    {
        $boolQuery = new \Elastica\Query\BoolQuery();

        $fieldQuery = new \Elastica\Query\Match();
        $fieldQuery->setFieldQuery('systematicName', $search);
        $fieldQuery->setFieldParam('systematicName', 'analyzer', 'custom_search_analyzer');
        $boolQuery->addShould($fieldQuery);

        $fieldQuery2 = new \Elastica\Query\Match();
        $fieldQuery2->setFieldQuery('usualName', $search);
        $fieldQuery2->setFieldParam('usualName', 'analyzer', 'custom_search_analyzer');
        $boolQuery->addShould($fieldQuery2);

        // build $query with Elastica objects
        return $this->find($boolQuery);
    }

    public function findByNamesWithCountry($search, $country)
    {
        $boolQuery = new \Elastica\Query\BoolQuery();

        $systematicNameQuery = new \Elastica\Query\Match();
        $systematicNameQuery->setFieldQuery('systematicName', $search);
        $systematicNameQuery->setFieldParam('systematicName', 'analyzer', 'custom_search_analyzer');
        $boolQuery->addShould($systematicNameQuery);

        $usualNameQuery = new \Elastica\Query\Match();
        $usualNameQuery->setFieldQuery('usualName', $search);
        $usualNameQuery->setFieldParam('usualName', 'analyzer', 'custom_search_analyzer');
        $boolQuery->addShould($usualNameQuery);

        $countryQuery = new \Elastica\Query\Terms();
        // Elastica index all in lowercase, but it try to mach uppercase on lowercase and never work
        // To-do: create index and search analyzer
        $countryQuery->setTerms('country', [strtolower($country)]);
        $boolQuery->addMust($countryQuery);

        // build $query with Elastica objects
        return $this->find($boolQuery);
    }
}
