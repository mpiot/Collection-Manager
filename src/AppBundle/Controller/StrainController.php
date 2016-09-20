<?php

namespace AppBundle\Controller;

use AppBundle\Entity\GmoStrain;
use AppBundle\Entity\WildStrain;
use AppBundle\Form\GmoStrainEditType;
use AppBundle\Form\GmoStrainType;
use AppBundle\Form\WildStrainEditType;
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
     * @Route("/view/gmo/{id}", name="strain_gmo_view")
     */
    public function viewGmoAction(GmoStrain $strain)
    {
        return $this->render('strain/view.html.twig', array(
            'strain' => $strain,
            'typeOfStrain' => 'gmo',
        ));
    }

    /**
     * @Route("/view/wild/{id}", name="strain_wild_view")
     */
    public function viewWildAction(WildStrain $strain)
    {
        return $this->render('strain/view.html.twig', array(
            'strain' => $strain,
            'typeOfStrain' => 'wild',
        ));
    }

    /**
     * @Route("/gmo/add", name="strain_gmo_add")
     */
    public function addGmoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $strain = new GmoStrain();
        $strainUsualNames = $em->getRepository('AppBundle:GmoStrain')->findAllUsualName();

        $form = $this->createForm(GmoStrainType::class, $strain);
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {

            // Manually persist the tube, because with cascade, doctrine want persist the strain before
            // but we need persist tube before, to generate the name of each tube
            // because the strain name is the name of the first tube
            foreach($strain->getTubes() as $tube) {
                $em->persist($tube);
            }

            $em->persist($strain);
            dump($strain->getTubes()->first()->getName());

            $em->flush();

            $this->addFlash('success', 'The strain has been added successfully: '.$strain->getSystematicName());

            return $this->redirectToRoute('strain_index');
        }

        return $this->render('strain/add.html.twig', array(
            'form' => $form->createView(),
            'strainUsualNames' => $strainUsualNames,
            'typeOfStrain' => 'gmo',
        ));
    }

    /**
     * @Route("/wild/add", name="strain_wild_add")
     */
    public function addWildAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $strain = new WildStrain();
        $strainUsualNames = $em->getRepository('AppBundle:WildStrain')->findAllUsualName();

        $form = $this->createForm(WildStrainType::class, $strain);

        $form->handleRequest($request);

        if ($form->isValid()) {
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
            'strainUsualNames' => $strainUsualNames,
            'typeOfStrain' => 'wild',
        ));
    }

    /**
     * @Route("/gmo/edit/{id}", name="strain_gmo_edit")
     */
    public function editGmoAction(GmoStrain $strain, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $strainUsualNames = $em->getRepository('AppBundle:GmoStrain')->findAllUsualName();

        $form = $this->createForm(GmoStrainEditType::class, $strain);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The strain has been edited successfully.');

            return $this->redirectToRoute('strain_gmo_view', ['id' => $strain->getId()]);
        }
        
        return $this->render('strain/edit.html.twig', array(
            'form' => $form->createView(),
            'strain' => $strain,
            'strainUsualNames' => $strainUsualNames,
            'typeOfStrain' => 'gmo',
        ));
    }

    /**
     * @Route("/wild/edit/{id}", name="strain_wild_edit")
     */
    public function editWildAction(WildStrain $strain, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $strainUsualNames = $em->getRepository('AppBundle:WildStrain')->findAllUsualName();

        $form = $this->createForm(WildStrainEditType::class, $strain);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The strain has been edited successfully.');

            return $this->redirectToRoute('strain_wild_view', ['id' => $strain->getId()]);
        }
        
        return $this->render('strain/edit.html.twig', array(
            'form' => $form->createView(),
            'strain' => $strain,
            'strainUsualNames' => $strainUsualNames,
            'typeOfStrain' => 'wild',
        ));
    }

    /**
     * @Route("/delete/gmo/{id}", name="strain_gmo_delete")
     */
    public function deleteGmoAction(GmoStrain $strain, Request $request)
    {
        if ($strain->getDeleted()) {
            $this->addFlash('warning', 'The strain is already deleted.');

            return $this->redirect($this->generateUrl('strain_index'));
        }

        $form = $this->createFormBuilder()->getForm();

        if ($form->handleRequest($request)->isValid()) {
            $strain->setDeleted(true);

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The strain has been deleted successfully.');

            return $this->redirect($this->generateUrl('strain_index'));
        }

        return $this->render('strain/delete.html.twig', array(
            'strain' => $strain,
            'form' => $form->createView(),
            'typeOfStrain' => 'gmo',
        ));
    }

    /**
     * @Route("/delete/wild/{id}", name="strain_wild_delete")
     */
    public function deleteWildAction(WildStrain $strain, Request $request)
    {
        if ($strain->getDeleted()) {
            $this->addFlash('warning', 'The strain is already deleted.');

            return $this->redirect($this->generateUrl('strain_index'));
        }
        $form = $this->createFormBuilder()->getForm();

        if ($form->handleRequest($request)->isValid()) {
            $strain->setDeleted(true);

            $em = $this->getDoctrine()->getManager();;
            $em->flush();

            $this->addFlash('success', 'The strain has been deleted successfully.');

            return $this->redirect($this->generateUrl('strain_index'));
        }

        return $this->render('strain/delete.html.twig', array(
            'strain' => $strain,
            'form' => $form->createView(),
            'typeOfStrain' => 'wild',
        ));
    }
}
