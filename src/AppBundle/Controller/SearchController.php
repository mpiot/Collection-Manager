<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Project;
use AppBundle\Entity\Type;
use AppBundle\Entity\User;
use AppBundle\Form\Type\AdvancedSearchType;
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
    /**
     * Quick search.
     * Do the search and return the results.
     *
     * @param $search
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/search/{keyword}", name="quick-search")
     */
    public function quickSearchAction($keyword)
    {
        // Get the query
        $query = $this->searchQuery($keyword);

        // Execute the query
        $finder = $this->container->get('fos_elastica.finder.app');
        $results = $finder->find($query, 50);

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
            $query = $this->searchQuery($data['search'],  $data['category'], $data['country'], $data['project'], $data['type'], $data['author'], $data['deleted']);

            // Execute the query
            $finder = $this->container->get('fos_elastica.finder.app');
            $results = $finder->find($query, 50);

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
        $query = $this->searchQuery($keyword);

        // Execute the query
        $index = $this->container->get('fos_elastica.index.app');
        $results = $index->search($query, 10)->getResults();

        $data= [];

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

    private function searchQuery($keyword = null, $category = null, $country = null, Project $project = null, Type $type = null, User $author = null, $deleted = false)
    {
        // Create the search query
        $query = new \Elastica\Query\BoolQuery();

        //-------------------------------//
        // Set queries used in BoolQuery //
        //-------------------------------//

        // If user set a keyword, else, he just want use a filter
        if (null !== $keyword) {
            $keywordQuery = new \Elastica\Query\QueryString();
            $keywordQuery->setFields(['autoName', 'name', 'sequence']);
            $keywordQuery->setDefaultOperator('AND');
            $keywordQuery->setQuery($keyword);
        }

        // Set deleted filter on false
        $deleted = $deleted ? 'true' : 'false';
        $deletedQuery = new \Elastica\Query\Term();
        $deletedQuery->setTerm('deleted', $deleted);

        // Set the country filter
        if (null !== $country && '' !== $country) {
            $countryQuery = new \Elastica\Query\Term();
            // We can't use an analyzer on a term, then we need to lower it here.
            $countryQuery->setTerm('country', strtolower($country));
        }

        // Set the project filter
        if (null !== $project && '' !== $project) {
            $projectQuery = new \Elastica\Query\Term();
            $projectQuery->setTerm('projects', $project->getId());
        }

        // Set the type filter
        if (null !== $type && '' !== $type) {
            $typeQuery = new \Elastica\Query\Term();
            $typeQuery->setTerm('type', $type->getId());
        }

        // Set the author filter
        if (null !== $author && '' !== $author) {
            $authorQuery = new \Elastica\Query\Term();
            $authorQuery->setTerm('author', $author->getId());
        }

        //----------------------------------------//
        // Set security queries used in BoolQuery //
        //----------------------------------------//

        // Set a team filter
        $teamsSecureQuery = new \Elastica\Query\Terms();
        $teamsSecureQuery->setTerms('team', $this->getUser()->getTeamsId());

        // Set a project filter
        $projectsSecureQuery = new \Elastica\Query\Terms();
        $projectsSecureQuery->setTerms('projects', $this->getUser()->getProjectsId());

        //-------------------------------------------//
        // Assign previous queries to each BoolQuery //
        //-------------------------------------------//

        // For plasmid
        if (null === $category || in_array('plasmid', $category)) {
            // Set a specific filter, for this type
            $plasmidTypeQuery = new \Elastica\Query\Type();
            $plasmidTypeQuery->setType('plasmid');

            // Create the BoolQuery, and set a MinNumShouldMatch, to avoid have all results in database
            $plasmidBoolQuery = new \Elastica\Query\BoolQuery();
            $plasmidBoolQuery->setMinimumNumberShouldMatch(1);

            // First, define required queries like: type, security
            $plasmidBoolQuery->addFilter($teamsSecureQuery);
            $plasmidBoolQuery->addFilter($plasmidTypeQuery);

            // Then, all conditional queries
            if (null !== $keyword) {
                $plasmidBoolQuery->addShould($keywordQuery);
            }
            if (null !== $author && '' !== $author) {
                $plasmidBoolQuery->addFilter($authorQuery);
            }

            // Add the Plasmid BoolQuery to the main BoolQuery
            // Set a boost on 2, because there is 2 fields in "should"
            $query->addShould($plasmidBoolQuery->setBoost(2));
        }

        // For primer
        if (null === $category || in_array('primer', $category)) {
            // Set a specific filter, for this type
            $primerTypeQuery = new \Elastica\Query\Type();
            $primerTypeQuery->setType('primer');

            // Create the BoolQuery, and set a MinNumShouldMatch, to avoid have all results in database
            $primerBoolQuery = new \Elastica\Query\BoolQuery();
            $primerBoolQuery->setMinimumNumberShouldMatch(1);

            // First, define required queries like: type, security
            $primerBoolQuery->addFilter($teamsSecureQuery);
            $primerBoolQuery->addFilter($primerTypeQuery);

            // Then, all conditional queries
            if (null !== $keyword) {
                $primerBoolQuery->addShould($keywordQuery);
            }
            if (null !== $author && '' !== $author) {
                $primerBoolQuery->addFilter($authorQuery);
            }

            // Add the Primer BoolQuery to the main BoolQuery
            // Set a boost on 3, because there is 3 fields in "should"
            $query->addShould($primerBoolQuery->setBoost(3));
        }

        // For Gmo Strain
        if (null === $category || in_array('gmo', $category)) {
            // Set a specific filter, for this type
            $gmoTypeQuery = new \Elastica\Query\Type();
            $gmoTypeQuery->setType('gmoStrain');

            // Create the BoolQuery, and set a MinNumShouldMatch, to avoid have all results in database
            $gmoStrainBoolQuery = new \Elastica\Query\BoolQuery();
            $gmoStrainBoolQuery->setMinimumNumberShouldMatch(1);

            // First, define required queries like: type, security
            $gmoStrainBoolQuery->addFilter($projectsSecureQuery);
            $gmoStrainBoolQuery->addFilter($gmoTypeQuery);

            // Then, all conditional queries
            if (null !== $keyword) {
                $gmoStrainBoolQuery->addShould($keywordQuery);
            }
            $gmoStrainBoolQuery->addFilter($deletedQuery);
            if (null !== $author && '' !== $author) {
                $gmoStrainBoolQuery->addFilter($authorQuery);
            }
            if (null !== $project && '' !== $project) {
                $gmoStrainBoolQuery->addFilter($projectQuery);
            }
            if (null !== $type && '' !== $type) {
                $gmoStrainBoolQuery->addFilter($typeQuery);
            }

            // Add the Gmo BoolQuery to the main BoolQuery
            // Set a boost on 2, because there is 2 fields in "should"
            $query->addShould($gmoStrainBoolQuery->setBoost(2));
        }

        // For wild strain
        if (null === $category || in_array('wild', $category)) {
            // Set a specific filter, for this type
            $wildTypeQuery = new \Elastica\Query\Type();
            $wildTypeQuery->setType('wildStrain');

            // Create the BoolQuery, and set a MinNumShouldMatch, to avoid have all results in database
            $wildStrainBoolQuery = new \Elastica\Query\BoolQuery();
            $wildStrainBoolQuery->setMinimumNumberShouldMatch(1);

            // First, define required queries like: type, security
            $wildStrainBoolQuery->addFilter($projectsSecureQuery);
            $wildStrainBoolQuery->addFilter($wildTypeQuery);

            // Then, all conditional queries
            if (null !== $keyword) {
                $wildStrainBoolQuery->addShould($keywordQuery);
            }
            $wildStrainBoolQuery->addFilter($deletedQuery);
            if (null !== $author && '' !== $author) {
                $wildStrainBoolQuery->addFilter($authorQuery);
            }
            if (null !== $country && '' !== $country) {
                $wildStrainBoolQuery->addFilter($countryQuery);
            }
            if (null !== $project && '' !== $project) {
                $wildStrainBoolQuery->addFilter($projectQuery);
            }
            if (null !== $type && '' !== $type) {
                $wildStrainBoolQuery->addFilter($typeQuery);
            }

            // Add the Gmo BoolQuery to the main BoolQuery
            // Set a boost on 2, because there is 2 fields in "should"
            $query->addShould($wildStrainBoolQuery->setBoost(2));
        }

        return $query;
    }
}
