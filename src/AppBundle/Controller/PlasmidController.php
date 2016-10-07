<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Plasmid;
use AppBundle\Form\PlasmidType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Utils\PlasmidGenBank;

/**
 * Class plasmidController
 * @package AppBundle\Controller
 * 
 * @Route("/plasmid")
 */
class PlasmidController extends Controller
{
    /**
     * @Route("/", name="plasmid_index")
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $plasmids = $em->getRepository('AppBundle:Plasmid')->findAll();

        return $this->render('plasmid/index.html.twig', array(
            'plasmids' => $plasmids,
        ));
    }

    /**
     * @Route("/view/{id}", name="plasmid_view")
     */
    public function viewAction(Plasmid $plasmid)
    {
        $gbk = new PlasmidGenBank($plasmid);

        return $this->render('plasmid/view.html.twig', array(
            'plasmid' => $plasmid,
            'gbkFile' => $gbk->getFile(),
            'gbk' => $gbk->getArray(),
        ));
    }

    /**
     * @Route("/add", name="plasmid_add")
     */
    public function addAction(Request $request)
    {
        $plasmid = new Plasmid();
        $form = $this->createForm(PlasmidType::class, $plasmid);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ((0 === $form->get('addGenBankFile')->getData()) && (null !== $plasmid->getGenBankFile())) {
                $em->persist($plasmid->getGenBankFile());
            }

            $em->persist($plasmid);
            $em->flush();

            $this->addFlash('success', 'The plasmid has been added successfully.');

            return $this->redirectToRoute('plasmid_index');
        }

        return $this->render('plasmid/add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/edit/{id}", name="plasmid_edit")
     */
    public function editAction(Plasmid $plasmid, Request $request)
    {
        $form = $this->createForm(PlasmidType::class, $plasmid);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ((0 === $form->get('addGenBankFile')->getData()) && (null !== $plasmid->getGenBankFile())) {
                $em->remove($plasmid->getGenBankFile());
                $plasmid->setGenBankFile(null);
            }

            $em->flush();

            $this->addFlash('success', 'The plasmid has been successfully edited.');

            return $this->redirectToRoute('plasmid_index');
        }

        return $this->render('plasmid/edit.html.twig', array(
            'plasmid' => $plasmid,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/delete/{id}", name="plasmid_delete")
     */
    public function deleteAction(Plasmid $plasmid, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($plasmid);
            $em->flush();

            $this->addFlash('success', 'The plasmid has been deleted successfully.');

            return $this->redirectToRoute('plasmid_index');
        }

        return $this->render('plasmid/delete.html.twig', array(
            'plasmid' => $plasmid,
            'form' => $form->createView(),
        ));
    }
}
