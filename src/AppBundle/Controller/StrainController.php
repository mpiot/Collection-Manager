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
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class StrainController extends Controller
{
    /**
     * @Route("/", name="strain_index")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $strains = $em->getRepository('AppBundle:Strain')->findAllForUser($this->getUser());

        // If the user have no projects
        if (!$this->getUser()->isProjectMember()) {
            $this->addFlash('warning', 'You must be a member of a project to submit a strain.');
        }

        return $this->render('strain/index.html.twig', [
            'strains' => $strains,
        ]);
    }

    /**
     * @Route("/add/gmo", name="strain_add_gmo")
     * @Security("user.isTeamAdministrator() or user.isProjectMember()")
     */
    public function addGmoAction(Request $request)
    {
        $strain = new Strain();
        $strain->setDiscriminator('gmo');
        $strain->setAuthor($this->getUser());

        $form = $this->createForm(StrainGmoType::class, $strain)
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

            $nextAction = $form->get('saveAndAdd')->isClicked()
                ? 'strain_add_gmo'
                : 'strain_index';

            return $this->redirectToRoute($nextAction);
        }

        return $this->render('strain/add.html.twig', [
            'form' => $form->createView(),
            'strain' => $strain,
        ]);
    }

    /**
     * @Route("/add/wild", name="strain_add_wild")
     * @Security("user.isTeamAdministrator() or user.isProjectMember()")
     */
    public function addWildAction(Request $request)
    {
        $strain = new Strain();
        $strain->setDiscriminator('wild');

        $form = $this->createForm(StrainWildType::class, $strain)
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
            $strain->setAuthor($this->getUser());
            $em->persist($strain);
            $em->flush();

            $this->addFlash('success', 'The strain has been added successfully: '.$strain->getAutoName());

            $nextAction = $form->get('saveAndAdd')->isClicked()
                ? 'strain_add_wild'
                : 'strain_index';

            return $this->redirectToRoute($nextAction);
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
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->container->get('fos_elastica.object_persister.app.strain')->replaceOne($strain);

            $this->addFlash('success', 'The strain has been edited successfully.');

            return $this->redirectToRoute('strain_view', [
                'id'=> $strain->getId(),
                'slug' => $strain->getSlug()
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
                'id'=> $strain->getId(),
                'slug' => $strain->getSlug()
            ]);
        }

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->remove($strain);
        $entityManager->flush();

        $this->addFlash('success', 'The strain has been deleted successfully.');

        return $this->redirectToRoute('strain_index');
    }

    /**
     * @Route("/{id}/parents", name="strain_parents", requirements={"id": "\d+"})
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function parentalParentsStrainsAction(Strain $strain)
    {
        $em = $this->getDoctrine()->getManager();
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

        $response = new Response(json_encode($array));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/{id}/children", name="strain_children", requirements={"id": "\d+"})
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function parentalChildrenStrainsAction(Strain $strain)
    {
        $em = $this->getDoctrine()->getManager();
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

        $response = new Response(json_encode($array));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/name-suggest/{keyword}", name="strain-name-suggest", options={"expose"=true}, condition="request.isXmlHttpRequest()")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
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
