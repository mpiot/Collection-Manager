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

use App\Entity\Strain;
use App\Entity\User;
use FOS\ElasticaBundle\Repository;

class StrainRepository extends Repository
{
    public function searchByNameQuery($q, $p, $groupId, User $user)
    {
        $query = new \Elastica\Query();
        $boolQuery = new \Elastica\Query\BoolQuery();

        $groupSecureQuery = new \Elastica\Query\Terms();
        $groupSecureQuery->setTerms('group_id', $user->getGroupsId());
        $boolQuery->addFilter($groupSecureQuery);

        // Only search in the type strain
        $typeQuery = new \Elastica\Query\Type();
        $typeQuery->setType('strain');
        $boolQuery->addFilter($typeQuery);

        if (null !== $q) {
            $queryString = new \Elastica\Query\QueryString();
            $queryString->setFields(['name', 'autoName', 'uniqueCode']);
            $queryString->setDefaultOperator('AND');
            $queryString->setQuery($q);

            $boolQuery->addMust($queryString);
            $query->setQuery($boolQuery);
        } else {
            $matchAllQuery = new \Elastica\Query\MatchAll();

            $boolQuery->addMust($matchAllQuery);
            $query->setQuery($boolQuery);
            $query->setSort(['name_raw' => 'asc']);
        }

        if (null !== $groupId) {
            $groupQuery = new \Elastica\Query\Term();
            $groupQuery->setTerm('group_id', $groupId);
            $boolQuery->addFilter($groupQuery);
        }

        if (null !== $p) {
            $query
                ->setFrom(($p - 1) * Strain::NUM_ITEMS)
                ->setSize(Strain::NUM_ITEMS);
        }

        // build $query with Elastica objects
        return $query;
    }
}
