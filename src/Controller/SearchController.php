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

namespace App\Controller;

use App\Form\Type\AdvancedSearchType;
use App\SearchRepository\GlobalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Search engine controller.
 */
class SearchController extends AbstractController
{
    const HITS_PER_PAGE = 50;

    /**
     * @Route("/search", name="quick-search", methods={"GET"})
     */
    public function quickSearchAction(Request $request)
    {
        $keyword = null !== $request->get('q') ? $request->get('q') : '';
        $keyword = mb_convert_encoding($keyword, 'UTF-8');

        // Get the query
        $repository = new GlobalRepository();
        $query = $repository->searchQuery($keyword, $this->getUser());

        // Execute the query
        $mngr = $this->get('fos_elastica.index_manager');
        $search = $mngr->getIndex('app')->createSearch();
        $search->addType('plasmid');
        $search->addType('primer');
        $search->addType('strain');
        $search->addType('product');
        $search->addType('equipment');
        $resultSet = $search->search($query, self::HITS_PER_PAGE);
        $transformer = $this->get('fos_elastica.elastica_to_model_transformer.collection.app');
        $results = $transformer->transform($resultSet->getResults());

        // Return the view
        return $this->render('search\quickSearch.html.twig', [
            'search' => $keyword,
            'results' => $results,
        ]);
    }

    /**
     * @Route("/advanced-search", name="advanced-search", methods={"GET", "POST"})
     */
    public function advancedSearchAction(Request $request)
    {
        $form = $this->createForm(AdvancedSearchType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Get the query
            $repository = new GlobalRepository();
            $query = $repository->searchQuery($data['search'], $this->getUser(), $data['category'], $data['country'], $data['group'], $data['author']);

            // Execute the query
            $mngr = $this->get('fos_elastica.index_manager');
            $search = $mngr->getIndex('app')->createSearch();
            $search->addType('plasmid');
            $search->addType('primer');
            $search->addType('strain');
            $search->addType('product');
            $search->addType('equipment');
            $resultSet = $search->search($query, self::HITS_PER_PAGE);
            $transformer = $this->get('fos_elastica.elastica_to_model_transformer.collection.app');
            $results = $transformer->transform($resultSet->getResults());

            // Return the view
            return $this->render('search\advancedSearch.html.twig', [
                'form' => $form->createView(),
                'results' => $results,
            ]);
        }

        return $this->render('search/advancedSearch.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
