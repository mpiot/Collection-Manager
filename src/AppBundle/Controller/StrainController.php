<?php

namespace AppBundle\Controller;

use AppBundle\Entity\GmoStrain;
use AppBundle\Entity\WildStrain;
use AppBundle\Form\GmoStrainType;
use AppBundle\Form\WildStrainType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class StrainController
 * @package AppBundle\Controller
 *
 * @Route("/strain")
 */
class StrainController extends Controller
{
    /**
     * @Route("/", name="strain_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $gmoStrains = $em->getRepository('AppBundle:GmoStrain')->findBy([], ['id' => 'DESC'], 10);
        $wildStrains = $em->getRepository('AppBundle:WildStrain')->findBy([], ['id' => 'DESC'], 10);
        
        return $this->render('strain/index.html.twig', array(
            'gmoStrains' => $gmoStrains,
            'wildStrains' => $wildStrains,
        ));
    }

    /**
     * @Route("/view/{id}", name="strain_view")
     */
    public function viewAction($id)
    {
        return $this->render('strain/view.html.twig', array(
            // ...
        ));
    }

    /**
     * @Route("/gmo/add", name="strain_gmo_add")
     */
    public function addGmoAction(Request $request)
    {
        $strain = new GmoStrain();
        $form = $this->createForm(GmoStrainType::class, $strain);
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            // Manually persist the tube, because with cascade, doctrine want persist the strain before
            // but we need persist tube before, to generate the name of each tube
            // because the strain name is the name of the first tube
            foreach($strain->getTubes() as $tube) {
                $em->persist($tube);
            }

            $em->persist($strain);
            $em->flush();

            $this->addFlash('success', 'The strain has been added successfully: '.$strain->getSystematicName());

            return $this->redirectToRoute('strain_index');
        }

        return $this->render('strain/add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/wild/add", name="strain_wild_add")
     */
    public function addWildAction(Request $request)
    {
        $strain = new WildStrain();
        $form = $this->createForm(WildStrainType::class, $strain);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // Manually persist the tube, because with cascade, doctrine want persist the strain before
            // but we need persist tube before, to generate the name of each tube
            // because the strain name is the name of the first tube
            foreach($strain->getTubes() as $tube) {
                $em->persist($tube);
            }
            
            $em->persist($strain);
            $em->flush();

            $this->addFlash('success', 'The strain has been added successfully: '.$strain->getSystematicName());

            return $this->redirectToRoute('strain_index');
        }
        
        return $this->render('strain/add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/gmo/edit/{id}", name="strain_gmo_edit")
     */
    public function editGmoAction(GmoStrain $strain, Request $request)
    {
        $form = $this->createForm(GmoStrainType::class, $strain);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The strain has been edited successfully.');

            return $this->redirectToRoute('strain_view', ['id' => $strain->getId()]);
        }
        
        return $this->render('strain/edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/wild/edit/{id}", name="strain_wild_edit")
     */
    public function editWildAction(WildStrain $strain, Request $request)
    {
        $form = $this->createForm(WildStrainType::class, $strain);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The strain has been edited successfully..');

            return $this->redirectToRoute('strain_view', ['id' => $strain->getId()]);
        }
        
        return $this->render('strain/edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/delete/{id}", name="strain_delete")
     */
    public function deleteAction($id)
    {
        return $this->render('strain/delete.html.twig', array(
            // ...
        ));
    }

}
