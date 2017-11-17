<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\AdvancedSearchType;
use AppBundle\SearchRepository\GlobalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * Search engine controller.
 *
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class SearchController extends Controller
{
    const HITS_PER_PAGE = 50;

    /**
     * @Route("/search", name="quick-search")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
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
     * @Route("/advanced-search", name="advanced-search")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function advancedSearchAction(Request $request)
    {
        $form = $this->createForm(AdvancedSearchType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Get the query
            $repository = new GlobalRepository();
            $query = $repository->searchQuery($data['search'], $this->getUser(), $data['category'], $data['country'], $data['team'], $data['author']);

            // Execute the query
            $mngr = $this->get('fos_elastica.index_manager');
            $search = $mngr->getIndex('app')->createSearch();
            $search->addType('plasmid');
            $search->addType('primer');
            $search->addType('strain');
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
