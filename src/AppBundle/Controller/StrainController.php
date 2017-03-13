<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Strain;
use AppBundle\Form\Type\StrainGmoType;
use AppBundle\Form\Type\StrainWildType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class StrainController.
 *
 * @Route("/strain")
 */
class StrainController extends Controller
{
    /**
     * @Route("/",
     *     options={"expose"=true},
     *     name="strain_index"
     * )
     * @Security("user.isProjectMember()")
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction($request);

        return $this->render('strain/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
            'projectRequest' => $request->get('project'),
        ]);
    }

    /**
     * @Route(
     *     "/list",
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()",
     *     name="strain_index_ajax"
     * )
     * @Security("user.isProjectMember()")
     */
    public function listAction(Request $request)
    {
        $query = ('' !== $request->get('q') && null !== $request->get('q')) ? $request->get('q') : null;
        $projectId = ('' !== $request->get('project') && null !== $request->get('project')) ? $request->get('project') : null;
        $page = (0 < (int) $request->get('p')) ? $request->get('p') : 1;

        $repositoryManager = $this->get('fos_elastica.manager.orm');
        $repository = $repositoryManager->getRepository('AppBundle:Strain');
        $elasticQuery = $repository->searchByNameQuery($query, $page, $projectId, $this->getUser());
        $strainList = $this->get('fos_elastica.finder.app.strain')->find($elasticQuery);
        $nbResults = $this->get('fos_elastica.index.app.strain')->count($elasticQuery);

        $nbPages = ceil($nbResults / Strain::NUM_ITEMS);

        return $this->render('strain/_list.html.twig', [
            'strainList' => $strainList,
            'query' => $query,
            'page' => $page,
            'nbPages' => $nbPages,
            'project' => $request->get('project'),
        ]);
    }

    /**
     * @Route("/add/gmo", name="strain_add_gmo")
     * @Route("/add/gmo/{id}-{slug}", name="strain_add_gmo_from_model", requirements={"id": "\d+"})
     * @Route("/add/wild", name="strain_add_wild")
     * @Security("user.isInTeam()")
     */
    public function addAction(Request $request, Strain $strainModel = null)
    {
        if ('strain_add_wild' === $request->get('_route')) {
            $discriminator = 'wild';
            $formType = StrainWildType::class;
        } else {
            $discriminator = 'gmo';
            $formType = StrainGmoType::class;
        }

        if ($strainModel) {
            $strain = clone $strainModel;
        } else {
            $strain = new Strain();
        }

        $strain->setDiscriminator($discriminator);

        $form = $this->createForm($formType, $strain)
            ->add('save', SubmitType::class, [
                'label' => 'Create',
                'attr' => [
                    'data-btn-group' => 'btn-group',
                    'data-btn-position' => 'btn-first',
                ],
            ])
            ->add('saveAndAdd', SubmitType::class, [
                'label' => 'Create and Add',
                'attr' => [
                    'data-btn-group' => 'btn-group',
                    'data-btn-position' => 'btn-last',
                ],
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($strain);
            $em->flush();

            $this->addFlash('success', 'The strain has been added successfully: '.$strain->getAutoName());

            if ($form->get('saveAndAdd')->isClicked()) {
                return $this->redirectToRoute('strain_add_'.$discriminator);
            } else {
                return $this->redirectToRoute('strain_view', ['id' => $strain->getId(), 'slug' => $strain->getSlug()]);
            }
        }

        return $this->render('strain/add.html.twig', [
            'form' => $form->createView(),
            'strain' => $strain,
        ]);
    }

    /**
     * @Route("/{id}-{slug}", name="strain_view", requirements={"id": "\d+"})
     * @ParamConverter("strain", options={"repository_method" = "findOneBySlug"})
     * @Security("is_granted('STRAIN_VIEW', strain)")
     */
    public function viewAction(Strain $strain)
    {
        return $this->render('strain/view.html.twig', [
            'strain' => $strain,
        ]);
    }

    /**
     * @Route("/{id}-{slug}/edit", name="strain_edit", requirements={"id": "\d+"})
     * @ParamConverter("strain", options={"repository_method" = "findOneBySlug"})
     * @Security("is_granted('STRAIN_EDIT', strain)")
     */
    public function editAction(Strain $strain, Request $request)
    {
        if ('gmo' === $strain->getDiscriminator()) {
            $form = $this->createForm(StrainGmoType::class, $strain);
        } else {
            $form = $this->createForm(StrainWildType::class, $strain);
        }

        $form->add('edit', SubmitType::class, [
            'label' => 'Edit',
            'attr' => [
                'data-btn-group' => 'btn-group',
                'data-btn-position' => 'btn-first',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->container->get('fos_elastica.object_persister.app.strain')->replaceOne($strain);

            $this->addFlash('success', 'The strain has been edited successfully.');

            return $this->redirectToRoute('strain_view', [
                'id' => $strain->getId(),
                'slug' => $strain->getSlug(),
            ]);
        }

        return $this->render('strain/edit.html.twig', [
            'form' => $form->createView(),
            'strain' => $strain,
        ]);
    }

    /**
     * @Route("/{id}-{slug}/delete", name="strain_delete", requirements={"id": "\d+"})
     * @ParamConverter("strain", options={"repository_method" = "findOneBySlug"})
     * @Method("POST")
     * @Security("is_granted('STRAIN_DELETE', strain)")
     */
    public function deleteAction(Strain $strain, Request $request)
    {
        // If the CSRF token is invalid, redirect user
        if (!$this->isCsrfTokenValid('strain_delete', $request->request->get('token'))) {
            $this->addFlash('warning', 'The CSRF token is invalid.');

            return $this->redirectToRoute('strain_view', [
                'id' => $strain->getId(),
                'slug' => $strain->getSlug(),
            ]);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $strain->setDeleted(true);
        $entityManager->persist($strain);
        $entityManager->flush();

        $this->addFlash('success', 'The strain has been deleted successfully.');

        return $this->redirectToRoute('strain_index');
    }

    /**
     * @Route("/{id}/parents", name="strain_parents", requirements={"id": "\d+"})
     * @Route("/{id}/children", name="strain_children", requirements={"id": "\d+"})
     */
    public function parentalStrainsAction(Strain $strain, Request $request)
    {
        $routeName = $request->get('_route');
        $em = $this->getDoctrine()->getManager();

        if ('strain_parents' === $routeName) {
            $strain = $em->getRepository('AppBundle:Strain')->findParents($strain);

            $array['name'] = $strain->getFullName();

            $c = 0;
            foreach ($strain->getParents() as $parent) {
                $array['children'][$c]['name'] = $parent->getFullName();

                foreach ($parent->getParents() as $parent2) {
                    $array['children'][$c]['children'][]['name'] = $parent2->getFullName();
                }

                ++$c;
            }
        } else {
            $strain = $em->getRepository('AppBundle:Strain')->findChildren($strain);

            $array['name'] = $strain->getFullName();

            $c = 0;
            foreach ($strain->getChildren() as $child) {
                $array['children'][$c]['name'] = $child->getFullName();

                foreach ($child->getChildren() as $child2) {
                    $array['children'][$c]['children'][]['name'] = $child2->getFullName();
                }

                ++$c;
            }
        }

        $response = new Response(json_encode($array));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/name-suggest/{keyword}", name="strain-name-suggest", options={"expose"=true}, condition="request.isXmlHttpRequest()")
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

        $data = [];

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
