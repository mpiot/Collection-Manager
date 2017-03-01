<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Box;
use AppBundle\Entity\Project;
use AppBundle\Form\Type\BoxEditType;
use AppBundle\Form\Type\BoxType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BoxController.
 *
 * @Route("/box")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class BoxController extends Controller
{
    /**
     * @Route(
     *     "/",
     *     options={"expose"=true},
     *     name="box_index"
     * )
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction($request);

        return $this->render('box/index.html.twig', [
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
     *     name="box_index_ajax"
     * )
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function listAction(Request $request)
    {
        $query = ('' !== $request->get('q') && null !== $request->get('q')) ? $request->get('q') : null;
        $projectId = ('' !== $request->get('project') && null !== $request->get('project')) ? $request->get('project') : null;
        $page = (0 < (int) $request->get('p')) ? $request->get('p') : 1;

        $repositoryManager = $this->get('fos_elastica.manager.orm');
        $repository = $repositoryManager->getRepository('AppBundle:Box');
        $elasticQuery = $repository->searchByNameQuery($query, $page, $projectId, $this->getUser());
        $boxList = $this->get('fos_elastica.finder.app.box')->find($elasticQuery);
        $nbResults = $this->get('fos_elastica.index.app.box')->count($elasticQuery);

        $nbPages = ceil($nbResults / Box::NUM_ITEMS);

        return $this->render('box/_list.html.twig', [
            'boxList' => $boxList,
            'query' => $query,
            'page' => $page,
            'nbPages' => $nbPages,
            'project' => $request->get('project'),
        ]);
    }

    /**
     * @Route("/{id}", name="box_view", requirements={"id": "\d+"})
     * @ParamConverter("box", class="AppBundle:Box", options={
     *     "repository_method" = "findOneWithProjectTypeTubesStrains"
     * })
     * @Security("is_granted('BOX_VIEW', box)")
     */
    public function viewAction(Box $box)
    {
        $tubesList = $box->getTubes()->toArray();
        $tubes = [];

        foreach ($tubesList as $tube) {
            $tubes[$tube->getCell()] = $tube;
        }

        return $this->render('box/view.html.twig', [
            'box' => $box,
            'tubes' => $tubes,
        ]);
    }

    /**
     * @Route("/add", name="box_add")
     * @Route("/add/{id}", name="box_add_4_project")
     * @ParamConverter("project", class="AppBundle:Project")
     * @Security("user.isTeamAdministrator() or user.isProjectMember()")
     */
    public function addAction(Request $request, Project $project = null)
    {
        $box = new Box();
        $box->setProject($project);
        $form = $this->createForm(BoxType::class, $box)
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
            $em->persist($box);
            $em->flush();

            $this->addFlash('success', 'The box has been added successfully.');

            $nextAction = $form->get('saveAndAdd')->isClicked()
                ? 'box_add'
                : 'box_index';

            return $this->redirectToRoute($nextAction);
        }

        return $this->render('box/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="box_edit")
     * @Security("is_granted('BOX_EDIT', box)")
     */
    public function editAction(Box $box, Request $request)
    {
        $form = $this->createForm(BoxEditType::class, $box);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The box has been edited successfully.');

            return $this->redirectToRoute('box_index');
        }

        return $this->render('box/edit.html.twig', [
            'box' => $box,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="box_delete")
     * @Method("POST")
     * @Security("is_granted('BOX_DELETE', box)")
     */
    public function deleteAction(Box $box, Request $request)
    {
        // If the box is already deleted
        if ($box->isDeleted()) {
            $this->addFlash('warning', 'The box has been already deleted.');

            return $this->redirectToRoute('box_index');
        }

        // If the CSRF token is invalid, redirect user
        if (!$this->isCsrfTokenValid('box_delete', $request->request->get('token'))) {
            $this->addFlash('warning', 'The CSRF token is invalid.');

            return $this->redirectToRoute('box_index');
        }

        $entityManager = $this->getDoctrine()->getManager();

        // If the box is not empty, soft delete it of the database
        if (!$box->getTubes()->isEmpty()) {
            $box->setDeleted(true);
        } else { // Else, delete it
            $entityManager->remove($box);
        }

        $entityManager->flush();

        $this->addFlash('success', 'The box has been deleted successfully.');

        return $this->redirectToRoute('box_index');
    }
}
