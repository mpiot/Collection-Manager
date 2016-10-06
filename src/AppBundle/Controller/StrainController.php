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
use Symfony\Component\HttpFoundation\Response;

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
     * @ParamConverter("GmoStrain", class="AppBundle:GmoStrain", options={
     *      "repository_method" = "findOneWithAll"
     * })
     */
    public function viewGmoAction(GmoStrain $strain)
    {
        return $this->render('strain/gmo/view.html.twig', array(
            'strain' => $strain,
        ));
    }

    /**
     * @Route("/view/wild/{id}", name="strain_wild_view")
     * @ParamConverter("WildStrain", class="AppBundle:WildStrain", options={
     *      "repository_method" = "findOneWithAll"
     * })
     */
    public function viewWildAction(WildStrain $strain)
    {
        return $this->render('strain/wild/view.html.twig', array(
            'strain' => $strain,
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
            $em->persist($strain);
            $em->flush();

            $this->addFlash('success', 'The strain has been added successfully: '.$strain->getSystematicName());

            return $this->redirectToRoute('strain_index');
        }

        return $this->render('strain/gmo/add.html.twig', array(
            'form' => $form->createView(),
            'strainUsualNames' => $strainUsualNames,
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
            $em->persist($strain);
            $em->flush();

            $this->addFlash('success', 'The strain has been added successfully: '.$strain->getSystematicName());

            return $this->redirectToRoute('strain_index');
        }
        
        return $this->render('strain/wild/add.html.twig', array(
            'form' => $form->createView(),
            'strainUsualNames' => $strainUsualNames,
        ));
    }

    /**
     * @Route("/gmo/edit/{id}", name="strain_gmo_edit")
     * @ParamConverter("gmoStrain", class="AppBundle:GmoStrain", options={
     *      "repository_method" = "findOneWithAll"
     * })
     */
    public function editGmoAction(GmoStrain $strain, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $strainUsualNames = $em->getRepository('AppBundle:GmoStrain')->findAllUsualName();

        $form = $this->createForm(GmoStrainType::class, $strain);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The strain has been edited successfully.');

            return $this->redirectToRoute('strain_gmo_view', ['id' => $strain->getId()]);
        }
        
        return $this->render('strain/gmo/edit.html.twig', array(
            'form' => $form->createView(),
            'strain' => $strain,
            'strainUsualNames' => $strainUsualNames,
        ));
    }

    /**
     * @Route("/wild/edit/{id}", name="strain_wild_edit")
     * @ParamConverter("WildStrain", class="AppBundle:WildStrain", options={
     *      "repository_method" = "findOneWithAll"
     * })
     */
    public function editWildAction(WildStrain $strain, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $strainUsualNames = $em->getRepository('AppBundle:WildStrain')->findAllUsualName();

        $form = $this->createForm(WildStrainType::class, $strain);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The strain has been edited successfully.');

            return $this->redirectToRoute('strain_wild_view', ['id' => $strain->getId()]);
        }
        
        return $this->render('strain/wild/edit.html.twig', array(
            'form' => $form->createView(),
            'strain' => $strain,
            'strainUsualNames' => $strainUsualNames,
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

        return $this->render('strain/gmo/delete.html.twig', array(
            'strain' => $strain,
            'form' => $form->createView(),
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

        return $this->render('strain/wild/delete.html.twig', array(
            'strain' => $strain,
            'form' => $form->createView(),
            'typeOfStrain' => 'wild',
        ));
    }

    /**
     * @Route("/parental/{id}", name="strain_parental")
     */
    public function parentalStrainsAction(GmoStrain $gmoStrain)
    {
        $em = $this->getDoctrine()->getManager();
        $strain = $em->getRepository('AppBundle:GmoStrain')->findParents($gmoStrain);

        $array['name'] = $strain->getFullName();

        $c = 0;
        foreach ($strain->getChildren() as $child) {
            $array['children'][$c]['name'] = $child->getFullName();

            foreach($child->getChildren() as $child2) {
                $array['children'][$c]['children'][]['name'] = $child2->getFullName();
            }

            $c++;
        }

        $response = new Response(json_encode($array));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
