<?php

namespace AppBundle\Controller;

use AppBundle\Entity\WildStrain;
use AppBundle\Form\Type\WildStrainType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class WildStrainController.
 *
 * @Route("/strain")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class WildStrainController extends Controller
{
    /**
     * @Route("/view/wild/{id}", name="strain_wild_view")
     * @ParamConverter("WildStrain", class="AppBundle:WildStrain", options={
     *      "repository_method" = "findOneWithAll"
     * })
     * @Security("is_granted('STRAIN_VIEW', strain)")
     */
    public function viewWildAction(WildStrain $strain)
    {
        return $this->render('strain/wild/view.html.twig', [
            'strain' => $strain,
        ]);
    }

    /**
     * @Route("/add/wild", name="strain_wild_add")
     * @Security("user.isTeamAdministrator() or user.isProjectAdministrator() or user.isProjectMember() or is_granted('ROLE_ADMIN')")
     */
    public function addWildAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $strain = new WildStrain();
        $strainNames = $em->getRepository('AppBundle:WildStrain')->findAllName();

        $form = $this->createForm(WildStrainType::class, $strain);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($strain);
            $em->flush();

            $this->addFlash('success', 'The strain has been added successfully: '.$strain->getAutoName());

            return $this->redirectToRoute('strain_index');
        }

        return $this->render('strain/wild/add.html.twig', [
            'form' => $form->createView(),
            'strainNames' => $strainNames,
        ]);
    }

    /**
     * @Route("/edit/wild/{id}", name="strain_wild_edit")
     * @ParamConverter("WildStrain", class="AppBundle:WildStrain", options={
     *      "repository_method" = "findOneWithAll"
     * })
     * @Security("is_granted('STRAIN_EDIT', strain)")
     */
    public function editWildAction(WildStrain $strain, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $strainNames = $em->getRepository('AppBundle:WildStrain')->findAllName();

        $form = $this->createForm(WildStrainType::class, $strain);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->container->get('fos_elastica.object_persister.app.wildstrain')->replaceOne($strain);

            $this->addFlash('success', 'The strain has been edited successfully.');

            return $this->redirectToRoute('strain_wild_view', ['id' => $strain->getId()]);
        }

        return $this->render('strain/wild/edit.html.twig', [
            'form' => $form->createView(),
            'strain' => $strain,
            'strainNames' => $strainNames,
        ]);
    }

    /**
     * @Route("/delete/wild/{id}", name="strain_wild_delete")
     * @Security("is_granted('STRAIN_DELETE', strain)")
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

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The strain has been deleted successfully.');

            return $this->redirectToRoute('strain_wild_view', ['id' => $strain->getId()]);
        }

        return $this->render('strain/wild/delete.html.twig', [
            'strain' => $strain,
            'form' => $form->createView(),
            'typeOfStrain' => 'wild',
        ]);
    }
}
