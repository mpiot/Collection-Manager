<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Search engine controller.
 */
class SearchController extends Controller
{
    /**
     * Quick search.
     * Do the search and return the results.
     * 
     * @param $search
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/search/{search}", name="quick-search")
     */
    public function quickSearchAction($search)
    {
        $repositoryManager = $this->container->get('fos_elastica.manager.orm');
        $repository = $repositoryManager->getRepository('AppBundle:GmoStrain');
        $results['gmo'] = $repository->quickSearch($search);

        $repository2 = $repositoryManager->getRepository('AppBundle:WildStrain');
        $results['wild'] = $repository2->quickSearch($search);

        return $this->render('search\quickSearchResults.html.twig', array(
            'search' => $search,
            'results' => $results,
        ));
    }
}
