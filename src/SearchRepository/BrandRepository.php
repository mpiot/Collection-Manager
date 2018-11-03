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

use App\Entity\Brand;
use FOS\ElasticaBundle\Repository;

class BrandRepository extends Repository
{
    public function searchByNameQuery($q, $p)
    {
        $boolQuery = new \Elastica\Query\BoolQuery();

        $query = new \Elastica\Query();
        $query->setQuery($boolQuery);

        // Only search in the type brand
        $typeQuery = new \Elastica\Query\Type();
        $typeQuery->setType('brand');
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
            ->setFrom(($p - 1) * Brand::NUM_ITEMS)
            ->setSize(Brand::NUM_ITEMS);

        // build $query with Elastica objects
        return $query;
    }
}
