<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Box;
use AppBundle\Form\BoxEditType;
use AppBundle\Form\BoxType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * @Route("/", name="box_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        if ($this->isGranted('ROLE_ADMIN')) {
            $boxes = $em->getRepository('AppBundle:Box')->findAllWithType();
        } else {
            $boxes = $em->getRepository('AppBundle:Box')->findAllAuthorizedForCurrentUserWithType($this->getUser());
        }

        return $this->render('box/index.html.twig', array(
            'boxes' => $boxes,
        ));
    }

    /**
     * @Route("/view/{id}", name="box_view")
     * @ParamConverter("box", class="AppBundle:Box", options={
     *     "repository_method" = "findOneWithProjectTypeTubesStrains"
     * })
     * @Security("is_granted('PROJECT_VIEW', box.getProject())")
     */
    public function viewAction(Box $box)
    {
        $tubesList = $box->getTubes()->toArray();
        $tubes = [];

        foreach ($tubesList as $tube) {
            $tubes[$tube->getCell()] = $tube;
        }

        return $this->render('box/view.html.twig', array(
            'box' => $box,
            'tubes' => $tubes,
        ));
    }

    /**
     * @Route("/add", name="box_add")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function addAction(Request $request)
    {
        $box = new Box();
        $form = $this->createForm(BoxType::class, $box);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($box);
            $em->flush();

            $this->addFlash('success', 'The box has been added successfully.');

            return $this->redirectToRoute('box_index');
        }

        return $this->render('box/add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/edit/{id}", name="box_edit")
     * @ParamConverter("box", class="AppBundle:Box", options={
     *      "repository_method" = "findOneWithType"
     * })
     * @Security("is_granted('PROJECT_EDIT', box.getProject())")
     */
    public function editAction(Box $box, Request $request)
    {
        $form = $this->createForm(BoxEditType::class, $box);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The box has been edited successfully.');

            return $this->redirectToRoute('box_index');
        }

        return $this->render('box/edit.html.twig', array(
            'box' => $box,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/delete/{id}", name="box_delete")
     * @Security("is_granted('PROJECT_DELETE', box.getProject())")
     */
    public function deleteAction(Box $box, Request $request)
    {
        if ($box->isDeleted()) {
            $this->addFlash('warning', 'The box has been already deleted.');

            return $this->redirectToRoute('box_index');
        }

        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // If the box is empty and is the last of a project, delete it of the database
            if (!$box->getTubes()->isEmpty() || !$box->isLastBox()) {
                $box->setDeleted(true);
            } else { // Else, softDelete it
                $em->remove($box);
            }

            $em->flush();

            $this->addFlash('success', 'The box has been deleted successfully.');

            return $this->redirectToRoute('box_index');
        }

        return $this->render('box/delete.html.twig', array(
            'box' => $box,
            'form' => $form->createView(),
        ));
    }
}
