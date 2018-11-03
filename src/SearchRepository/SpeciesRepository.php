<?php

/*
 * Copyright 2016-2018 Mathieu Piot.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\SearchRepository;

use App\Entity\Species;
use FOS\ElasticaBundle\Repository;

class SpeciesRepository extends Repository
{
    public function searchByScientificNameQuery($q, $p)
    {
        $query = new \Elastica\Query();
        $boolQuery = new \Elastica\Query\BoolQuery();
        $query->setQuery($boolQuery);

        // Only search in the type type
        $typeQuery = new \Elastica\Query\Type();
        $typeQuery->setType('species');
        $boolQuery->addFilter($typeQuery);

        if (null !== $q) {
            $queryString = new \Elastica\Query\QueryString();
            $queryString->setFields(['name']);
            $queryString->setDefaultOperator('AND');
            $queryString->setQuery($q);

            $boolQuery->addMust($queryString);
        } else {
            $matchAllQuery = new \Elastica\Query\MatchAll();

            $boolQuery->addMust($matchAllQuery);
            $query->setSort(['name_raw' => 'asc']);
        }

        $query
            ->setFrom(($p - 1) * Species::NUM_ITEMS)
            ->setSize(Species::NUM_ITEMS);

        // build $query with Elastica objects
        return $query;
    }
}
