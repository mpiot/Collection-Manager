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

        $gmoRepository = $repositoryManager->getRepository('AppBundle:GmoStrain');
        $results['gmo'] = $gmoRepository->search($search, $this->getUser()->getProjectsId());

        $wildRepository = $repositoryManager->getRepository('AppBundle:WildStrain');
        $results['wild'] = $wildRepository->search($search, $this->getUser()->getProjectsId());

        $plasmidRepository = $repositoryManager->getRepository('AppBundle:Plasmid');
        $results['plasmid'] = $plasmidRepository->search($search, $this->getUser()->getTeamsId());

        $primerRepository = $repositoryManager->getRepository('AppBundle:Primer');
        $results['primer'] = $primerRepository->search($search, $this->getUser()->getTeamsId());

        return $this->render('search\quickSearch.html.twig', [
            'search' => $search,
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

            $repositoryManager = $this->container->get('fos_elastica.manager.orm');

            $results = [];

            // Search for GmoStrain
            if (in_array('gmo', $data['category'])) {
                $gmoRepository = $repositoryManager->getRepository('AppBundle:GmoStrain');
                $results['gmo'] = $gmoRepository->search($data['search'], $this->getUser()->getProjectsId(), $data['deleted'], null, $data['project'], $data['type'], $data['author']);
            }

            // Search for WildStrain
            if (in_array('wild', $data['category'])) {
                // Define the repository
                $wildRepository = $repositoryManager->getRepository('AppBundle:WildStrain');
                $results['wild'] = $wildRepository->search($data['search'], $this->getUser()->getProjectsId(), $data['deleted'], $data['country'], $data['project'], $data['type'], $data['author']);
            }

            // Search for Plasmids
            if (in_array('plasmid', $data['category'])) {
                // Define the repository
                $plasmidRepository = $repositoryManager->getRepository('AppBundle:Plasmid');
                $results['plasmid'] = $plasmidRepository->search($data['search'], $this->getUser()->getTeamsId(), $data['author']);
            }

            // Search for Primers
            if (in_array('primer', $data['category'])) {
                // Define the repository
                $primerRepository = $repositoryManager->getRepository('AppBundle:Primer');
                $results['primer'] = $primerRepository->search($data['search'], $this->getUser()->getTeamsId(), $data['author']);
            }

            return $this->render('search/advancedSearch.html.twig', [
                'form' => $form->createView(),
                'results' => $results,
            ]);
        }

        return $this->render('search/advancedSearch.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
