<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\AdvancedSearchType;
use AppBundle\SearchRepository\GlobalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Search engine controller.
 *
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class SearchController extends Controller
{
    const HITS_PER_PAGE = 50;
    const SUGGEST_HITS = 10;

    /**
     * Quick search.
     * Do the search and return the results.
     *
     * @param $search
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/search/{keyword}", options={"expose"=true}, name="quick-search")
     */
    public function quickSearchAction($keyword)
    {
        // Get the query
        $repository = new GlobalRepository();
        $query = $repository->searchQuery($keyword, $this->getUser());

        // Execute the query
        $mngr = $this->get('fos_elastica.index_manager');
        $search = $mngr->getIndex('app')->createSearch();
        $search->addType('plasmid');
        $search->addType('primer');
        $search->addType('gmoStrain');
        $search->addType('wildStrain');
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
     * @param Request $request
     *
     * @Route("/advanced-search", name="advanced-search")
     */
    public function advancedSearchAction(Request $request)
    {
        $form = $this->createForm(AdvancedSearchType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Get the query
            $repository = new GlobalRepository();
            $query = $repository->searchQuery($data['search'], $this->getUser(), $data['category'], $data['country'], $data['project'], $data['type'], $data['author'], $data['deleted']);

            // Execute the query
            $mngr = $this->get('fos_elastica.index_manager');
            $search = $mngr->getIndex('app')->createSearch();
            $search->addType('plasmid');
            $search->addType('primer');
            $search->addType('gmoStrain');
            $search->addType('wildStrain');
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

    /**
     * @Route("/suggest-search", options={"expose"=true}, condition="request.isXmlHttpRequest()", name="suggest-search")
     */
    public function suggestAction(Request $request)
    {
        $keyword = $request->get('search', null);

        // Get the query
        $repository = new GlobalRepository();
        $query = $repository->searchQuery($keyword, $this->getUser());

        // Execute the query
        $mngr = $this->get('fos_elastica.index_manager');
        $search = $mngr->getIndex('app')->createSearch();
        $search->addType('plasmid');
        $search->addType('primer');
        $search->addType('gmoStrain');
        $search->addType('wildStrain');
        $results = $search->search($query, self::SUGGEST_HITS);

        $data = [];

        foreach ($results as $result) {
            $source = $result->getSource();
            $data[] = [
                'suggest' => $source['name'].' ('.$source['autoName'].')',
                'autoName' => $source['autoName'],
                'name' => $source['name'],
            ];
        }

        return new JsonResponse($data, 200, [
            'Cache-Control' => 'no-cache',
        ]);
    }
}
