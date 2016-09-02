<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Project;
use AppBundle\Form\ProjectType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProjectController
 * @package AppBundle\Controller
 *
 * @Route("/projet")
 */
class ProjectController extends Controller
{
    /**
     * @Route("/", name="project_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $projects = $em->getRepository('AppBundle:Project')->findBy([], ['name' => 'ASC']);

        return $this->render('project/index.html.twig', array(
            'projects' => $projects,
        ));
    }

    /**
     * @Route("/add", name="project_add")
     */
    public function addAction(Request $request)
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($project);
            $em->flush();

            $this->addFlash('success', 'The project has been added successfully.');

            return $this->redirectToRoute('project_index');
        }

        return $this->render('project/add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/edit/{id}", name="project_edit")
     */
    public function editAction(Project $project, Request $request)
    {
        $form = $this->createForm(ProjectType::class, $project);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The project has been edited successfully.');

            return $this->redirectToRoute('project_index');
        }

        return $this->render('project/edit.html.twig', array(
            'project' => $project,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/delete/{id}", name="project_delete")
     */
    public function deleteAction(Project $project, Request $request)
    {
        // On crée un formulaire vide, qui contiendra un champ anti CSRF
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
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
