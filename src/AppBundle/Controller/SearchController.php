<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\AdvancedSearchType;
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
        $results['gmo'] = $repository->search($search, $this->getUser()->getProjectsId());

        $repository2 = $repositoryManager->getRepository('AppBundle:WildStrain');
        $results['wild'] = $repository2->search($search, $this->getUser()->getProjectsId());

        return $this->render('search\quickSearch.html.twig', array(
            'search' => $search,
            'results' => $results,
        ));
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

            $repositoryManager = $this->container->get('fos_elastica.manager.orm');

            $results = [];

            // Search for GmoStrain
            if (in_array('gmo', $data['strainCategory'])) {
                $gmoRepository = $repositoryManager->getRepository('AppBundle:GmoStrain');
                $results['gmo'] = $gmoRepository->search($data['search'], $this->getUser()->getProjectsId(), $data['deleted'], null, $data['project'], $data['type']);
            }

            // Search for WildStrain
            if (in_array('wild', $data['strainCategory'])) {
                // Define the repository
                $wildRepository = $repositoryManager->getRepository('AppBundle:WildStrain');
                $results['wild'] = $wildRepository->search($data['search'], $this->getUser()->getProjectsId(), $data['deleted'], $data['country'], $data['project'], $data['type']);
            }

            return $this->render('search/advancedSearch.html.twig', array(
                'form' => $form->createView(),
                'results' => $results,
            ));
        }

        return $this->render('search/advancedSearch.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
