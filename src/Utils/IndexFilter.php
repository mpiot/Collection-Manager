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

namespace App\Utils;

use App\Entity\Box;
use App\Entity\Brand;
use App\Entity\Equipment;
use App\Entity\Group;
use App\Entity\Plasmid;
use App\Entity\Primer;
use App\Entity\Product;
use App\Entity\Seller;
use App\Entity\Species;
use App\Entity\Strain;
use App\Entity\User;
use Elastica\Index;
use Elastica\Query;
use FOS\ElasticaBundle\Finder\FinderInterface;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class IndexFilter
{
    const ALLOWED_CLASS = [
        Box::class,
        Brand::class,
        Equipment::class,
        Plasmid::class,
        Primer::class,
        Product::class,
        Seller::class,
        Species::class,
        Strain::class,
        User::class,
    ];
    const ALLOWED_FILTERED_CLASS = [Group::class];

    private $repositoryManager;
    private $finder;
    private $index;
    private $tokenStorage;
    private $requestStack;

    public function __construct(RepositoryManagerInterface $repositoryManager, FinderInterface $finder, Index $index, TokenStorageInterface $tokenStorage, RequestStack $requestStack)
    {
        $this->repositoryManager = $repositoryManager;
        $this->finder = $finder;
        $this->index = $index;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
    }

    public function filter($class, $searchField = true, $paginatedResults = true, $filteredClass = [])
    {
        $parameters = $this->getParameters($searchField, $paginatedResults, $filteredClass);
        $results = $this->getResults($class, $parameters);

        return $results;
    }

    private function getParameters($searchField, $paginatedResults, $filteredClass)
    {
        $parameters = [];
        $request = $this->requestStack->getCurrentRequest();

        if (true === $searchField) {
            $parameters['query'] = ('' !== $request->get('q') && null !== $request->get('q')) ? $request->get('q') : null;
        }

        if (true === $paginatedResults) {
            $parameters['page'] = (0 < (int) $request->get('p')) ? $request->get('p') : 1;
        }

        $parameters['filters'] = (object) $this->generateClassFilters($filteredClass);

        return (object) $parameters;
    }

    private function generateClassFilters($filteredClass)
    {
        $filter = [];
        $request = $this->requestStack->getCurrentRequest();

        if (!empty($filteredClass)) {
            foreach ($filteredClass as $class) {
                if (!\in_array($class, self::ALLOWED_FILTERED_CLASS, true)) {
                    throw new \RuntimeException('There is no filter for the class '.$class.'.');
                }

                if (Group::class === $class) {
                    $filter['group'] = ('' !== $request->get('group') && null !== $request->get('group')) ? $request->get('group') : $this->tokenStorage->getToken()->getUser()->getFavoriteGroup()->getId();
                }
            }
        }

        return $filter;
    }

    private function getResults($class, \stdClass $parameters)
    {
        if (!\in_array($class, self::ALLOWED_CLASS, true)) {
            throw new \RuntimeException('The class '.$class.' is not supported by the IndexFilter util.');
        }

        $results = $parameters;

        $elasticQuery = $this->getElasticQuery($class, $parameters);

        if (isset($parameters->page)) {
            $results->nbPages = $this->getNbPages($class, $elasticQuery);
        }

        $results->results = $this->finder->find($elasticQuery);

        return $results;
    }

    private function getElasticQuery($class, \stdClass $parameters)
    {
        $repositoryManager = $this->repositoryManager;
        $repository = $repositoryManager->getRepository($class);

        switch ($class) {
            case Box::class:
                $elasticQuery = $repository->searchByNameQuery($parameters->query, $parameters->page, $parameters->filters->group, $this->tokenStorage->getToken()->getUser());

                break;

            case Brand::class:
                $elasticQuery = $repository->searchByNameQuery($parameters->query, $parameters->page);

                break;

            case Equipment::class:
                $elasticQuery = $repository->searchByNameQuery($parameters->query, $parameters->page, $parameters->filters->group, $this->tokenStorage->getToken()->getUser());

                break;

            case Plasmid::class:
                $elasticQuery = $repository->searchByNameQuery($parameters->query, $parameters->page, $parameters->filters->group, $this->tokenStorage->getToken()->getUser());

                break;

            case Primer::class:
                $elasticQuery = $repository->searchByNameQuery($parameters->query, $parameters->page, $parameters->filters->group, $this->tokenStorage->getToken()->getUser());

                break;

            case Product::class:
                $elasticQuery = $repository->searchByNameQuery($parameters->query, $parameters->page, $parameters->filters->group, $this->tokenStorage->getToken()->getUser());

                break;

            case Seller::class:
                $elasticQuery = $repository->searchByNameQuery($parameters->query, $parameters->page);

                break;

            case Species::class:
                $elasticQuery = $repository->searchByScientificNameQuery($parameters->query, $parameters->page);

                break;

            case Strain::class:
                $elasticQuery = $repository->searchByNameQuery($parameters->query, $parameters->page, $parameters->filters->group, $this->tokenStorage->getToken()->getUser());

                break;

            case User::class:
                $elasticQuery = $repository->searchByNameQuery($parameters->query, $parameters->page, $this->tokenStorage->getToken()->getUser());

                break;

            default:
                $elasticQuery = null;
        }

        return $elasticQuery;
    }

    private function getNbPages($class, Query $elasticQuery)
    {
        $nbResults = $this->index->count($elasticQuery);
        $nbPages = ceil($nbResults / $class::NUM_ITEMS);

        return $nbPages;
    }
}
