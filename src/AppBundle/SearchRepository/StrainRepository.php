<?php

namespace AppBundle\SearchRepository;

use FOS\ElasticaBundle\Repository;

class StrainRepository extends Repository
{
    public function quickSearch($searchText)
    {
        $boolQuery = new \Elastica\Query\BoolQuery();

        $fieldQuery = new \Elastica\Query\Match();
        $fieldQuery->setFieldQuery('systematicName', $searchText);
        $fieldQuery->setFieldParam('systematicName', 'analyzer', 'custom_search_analyzer');
        $boolQuery->addShould($fieldQuery);

        $fieldQuery2 = new \Elastica\Query\Match();
        $fieldQuery2->setFieldQuery('usualName', $searchText);
        $fieldQuery2->setFieldParam('usualName', 'analyzer', 'custom_search_analyzer');
        $boolQuery->addShould($fieldQuery2);

        // build $query with Elastica objects
        return $this->find($boolQuery);
    }
}
