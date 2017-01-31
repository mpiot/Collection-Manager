<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Project;
use AppBundle\Form\Type\ProjectType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProjectController.
 *
 * @Route("/project")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class ProjectController extends Controller
{
    const HIT_PER_PAGE = 10;

    /**
     * @Route("/",
     *     options={"expose"=true},
     *     name="project_index"
     * )
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction($request);

        return $this->render('project/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
        ]);
    }

    /**
     * @Route(
     *     "/list",
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()",
     *     name="project_index_ajax"
     * )
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function listAction(Request $request)
    {
        $query = ('' !== $request->get('q') && null !== $request->get('q')) ? $request->get('q') : null;
        $page = (0 < (int) $request->get('p')) ? $request->get('p') : 1;

        $repositoryManager = $this->get('fos_elastica.manager.orm');
        $repository = $repositoryManager->getRepository('AppBundle:Project');
        $elasticQuery = $repository->searchByNameQuery($query, $page, self::HIT_PER_PAGE, $this->getUser());
        $nbResults = $this->get('fos_elastica.index.app.project')->count($elasticQuery);
        $finder = $this->get('fos_elastica.finder.app.project');
        $projectList = $finder->find($elasticQuery);

        $nbPages = ceil($nbResults / self::HIT_PER_PAGE);

        return $this->render('project/list.html.twig', [
            'projectList' => $projectList,
            'query' => $query,
            'page' => $page,
            'nbPages' => $nbPages,
        ]);
    }

    /**
     * @Route("/add", name="project_add")
     * @Security("user.isTeamAdministrator() or is_granted('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project)
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
                ],
            ])
            ->add('saveAndAddBox', SubmitType::class, [
                'label' => 'Create and Add a box',
                'attr' => [
                    'data-btn-group' => 'btn-group',
                    'data-btn-position' => 'btn-last',
                ],
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($project);
            $em->flush();

            $this->addFlash('success', 'The project has been added successfully. Now you may create one or more boxe(s).');

            if ($form->get('saveAndAdd')->isClicked()) {
                return $this->redirectToRoute('project_add');
            } elseif ($form->get('saveAndAddBox')->isClicked()) {
                return $this->redirectToRoute('box_add_4_project', ['id' => $project->getId()]);
            } else {
                return $this->redirectToRoute('project_index');
            }
        }

        return $this->render('project/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="project_edit")
     * @Security("is_granted('PROJECT_EDIT', project)")
     */
    public function editAction(Project $project, Request $request)
    {
        $form = $this->createForm(ProjectType::class, $project);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The project has been edited unsuccessfully.');

            return $this->redirectToRoute('project_index');
        }

        return $this->render('project/edit.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="project_delete")
     * @Security("is_granted('PROJECT_DELETE', project)")
     */
    public function deleteAction(Project $project, Request $request)
    {
        // Check if the project is empty, else redirect user
        if (!$project->getBoxes()->isEmpty()) {
            $this->addFlash('warning', 'The project cannot be deleted, there are boxes attached.');

            return $this->redirectToRoute('project_index');
        }

        // On crée un formulaire vide, qui contiendra un champ anti CSRF
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($project);
            $em->flush();

            $this->addFlash('success', 'The project has been deleted successfully.');

            return $this->redirectToRoute('project_index');
        }

        return $this->render('project/delete.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }
}
