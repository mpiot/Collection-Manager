<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Project;
use AppBundle\Form\Type\ProjectType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProjectController.
 *
 * @Route("/project")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class ProjectController extends Controller
{
    /**
     * @Route("/", name="project_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $projects = $em->getRepository('AppBundle:Project')->findAllAuthorizedForCurrentUser($this->getUser());

        return $this->render('project/index.html.twig', array(
            'projects' => $projects,
        ));
    }

    /**
     * @Route("/add", name="project_add")
     * @Security("user.isTeamAdministrator() or is_granted('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($project);
            $em->flush();

            $this->addFlash('success', 'The project has been added successfully. Now you may create one or more boxe(s).');

            return $this->redirectToRoute('box_add_4_project', ['id' => $project->getId()]);
        }

        return $this->render('project/add.html.twig', array(
            'form' => $form->createView(),
        ));
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

        return $this->render('project/edit.html.twig', array(
            'project' => $project,
            'form' => $form->createView(),
        ));
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

        return $this->render('project/delete.html.twig', array(
            'project' => $project,
            'form' => $form->createView(),
        ));
    }
}
