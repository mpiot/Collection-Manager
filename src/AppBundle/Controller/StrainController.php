<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class StrainController.
 *
 * @Route("/strain")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class StrainController extends Controller
{
    /**
     * @Route("/", name="strain_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $gmoStrains = $em->getRepository('AppBundle:GmoStrain')->findAllForUser($this->getUser());
        $wildStrains = $em->getRepository('AppBundle:WildStrain')->findAllForUser($this->getUser());

        // If the user have no projects
        if (!$this->getUser()->isProjectMember()) {
            $this->addFlash('warning', 'You must be a member of a project to submit a strain.');
        }

        return $this->render('strain/index.html.twig', [
            'gmoStrains' => $gmoStrains,
            'wildStrains' => $wildStrains,
        ]);
    }

    /**
     * @Route("/name-suggest/{keyword}", options={"expose"=true}, condition="request.isXmlHttpRequest()", name="strain-name-suggest")
     */
    public function nameSuggestAction($keyword)
    {
        // Set a project filter
        $projectsSecureQuery = new \Elastica\Query\Terms();
        $projectsSecureQuery->setTerms('projects', $this->getUser()->getProjectsId());

        // If user set a keyword, else, he just want use a filter
        $keywordQuery = new \Elastica\Query\QueryString();
        $keywordQuery->setFields(['autoName', 'name', 'sequence']);
        $keywordQuery->setDefaultOperator('AND');
        $keywordQuery->setQuery($keyword);

        $query = new \Elastica\Query\BoolQuery();
        $query->addFilter($projectsSecureQuery);
        $query->addMust($keywordQuery);

        // Execute the query
        $mngr = $this->container->get('fos_elastica.index_manager');
        $search = $mngr->getIndex('app')->createSearch();
        $search->addType('gmoStrain');
        $search->addType('wildStrain');
        $results = $search->search($query, 10)->getResults();

        $data= [];

        foreach ($results as $result) {
            $source = $result->getSource();
            // Verify if the name is already in the array
            if (!in_array($source['name'], $data)) {
                $data[] = $source['name'];
            }
        }

        return new JsonResponse($data, 200, [
            'Cache-Control' => 'no-cache',
        ]);
    }
}
