<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Box;
use AppBundle\Form\BoxEditType;
use AppBundle\Form\BoxType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BoxController
 * @package AppBundle\Controller
 *
 * @Route("/box")
 */
class BoxController extends Controller
{
    /**
     * @Route("/", name="box_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $boxes = $em->getRepository('AppBundle:Box')->findAllWithType();

        return $this->render('box/index.html.twig', array(
            'boxes' => $boxes,
        ));
    }

    /**
     * @Route("/view/{id}", name="box_view")
     * @ParamConverter("box", class="AppBundle:Box", options={
     *     "repository_method" = "findOneWithProjectTypeTubesStrains"
     * })
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
            "repository_method" = "findOneWithType"
     * })
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
     */
    public function deleteAction(Box $box, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($box);
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
