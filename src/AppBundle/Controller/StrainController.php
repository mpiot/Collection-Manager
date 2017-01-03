<?php

namespace AppBundle\Controller;

use AppBundle\Entity\GmoStrain;
use AppBundle\Entity\WildStrain;
use AppBundle\Form\Type\GmoStrainType;
use AppBundle\Form\Type\WildStrainType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class StrainController.
 *
 * @Route("/strain")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class StrainController extends Controller
{
    /**
     * @Route("/", name="strain_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $gmoStrains = $em->getRepository('AppBundle:GmoStrain')->findAllForUser($this->getUser());
        $wildStrains = $em->getRepository('AppBundle:WildStrain')->findAllForUser($this->getUser());

        // If the user have no projects
        if (!$this->getUser()->isProjectMember()) {
            $this->addFlash('warning', 'You must be a member of a project to submit a strain.');
        }

        return $this->render('strain/index.html.twig', [
            'gmoStrains' => $gmoStrains,
            'wildStrains' => $wildStrains,
        ]);
    }

    /**
     * @Route("/view/gmo/{id}", name="strain_gmo_view")
     * @ParamConverter("GmoStrain", class="AppBundle:GmoStrain", options={
     *      "repository_method" = "findOneWithAll"
     * })
     * @Security("is_granted('STRAIN_VIEW', strain)")
     */
    public function viewGmoAction(GmoStrain $strain)
    {
        return $this->render('strain/gmo/view.html.twig', [
            'strain' => $strain,
        ]);
    }

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
     * @Route("/gmo/add", name="strain_gmo_add")
     * @Security("user.isTeamAdministrator() or user.isProjectAdministrator() or user.isProjectMember() or is_granted('ROLE_ADMIN')")
     */
    public function addGmoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $strain = new GmoStrain();

        $strainUsualNames = $em->getRepository('AppBundle:GmoStrain')->findAllUsualName();

        $form = $this->createForm(GmoStrainType::class, $strain);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($strain);
            $em->flush();

            $this->addFlash('success', 'The strain has been added successfully: '.$strain->getSystematicName());

            return $this->redirectToRoute('strain_index');
        }

        return $this->render('strain/gmo/add.html.twig', [
            'form' => $form->createView(),
            'strainUsualNames' => $strainUsualNames,
        ]);
    }

    /**
     * @Route("/wild/add", name="strain_wild_add")
     * @Security("user.isTeamAdministrator() or user.isProjectAdministrator() or user.isProjectMember() or is_granted('ROLE_ADMIN')")
     */
    public function addWildAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $strain = new WildStrain();
        $strainUsualNames = $em->getRepository('AppBundle:WildStrain')->findAllUsualName();

        $form = $this->createForm(WildStrainType::class, $strain);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($strain);
            $em->flush();

            $this->addFlash('success', 'The strain has been added successfully: '.$strain->getSystematicName());

            return $this->redirectToRoute('strain_index');
        }

        return $this->render('strain/wild/add.html.twig', [
            'form' => $form->createView(),
            'strainUsualNames' => $strainUsualNames,
        ]);
    }

    /**
     * @Route("/gmo/edit/{id}", name="strain_gmo_edit")
     * @ParamConverter("gmoStrain", class="AppBundle:GmoStrain", options={
     *      "repository_method" = "findOneWithAll"
     * })
     * @Security("is_granted('STRAIN_EDIT', strain)")
     */
    public function editGmoAction(GmoStrain $strain, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $strainUsualNames = $em->getRepository('AppBundle:GmoStrain')->findAllUsualName();

        $form = $this->createForm(GmoStrainType::class, $strain);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->container->get('fos_elastica.object_persister.app.gmostrain')->replaceOne($strain);

            $this->addFlash('success', 'The strain has been edited successfully.');

            return $this->redirectToRoute('strain_gmo_view', ['id' => $strain->getId()]);
        }

        return $this->render('strain/gmo/edit.html.twig', [
            'form' => $form->createView(),
            'strain' => $strain,
            'strainUsualNames' => $strainUsualNames,
        ]);
    }

    /**
     * @Route("/wild/edit/{id}", name="strain_wild_edit")
     * @ParamConverter("WildStrain", class="AppBundle:WildStrain", options={
     *      "repository_method" = "findOneWithAll"
     * })
     * @Security("is_granted('STRAIN_EDIT', strain)")
     */
    public function editWildAction(WildStrain $strain, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $strainUsualNames = $em->getRepository('AppBundle:WildStrain')->findAllUsualName();

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
            'strainUsualNames' => $strainUsualNames,
        ]);
    }

    /**
     * @Route("/delete/gmo/{id}", name="strain_gmo_delete")
     * @Security("is_granted('STRAIN_DELETE', strain)")
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

        return $this->render('strain/gmo/delete.html.twig', [
            'strain' => $strain,
            'form' => $form->createView(),
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

            return $this->redirect($this->generateUrl('strain_index'));
        }

        return $this->render('strain/wild/delete.html.twig', [
            'strain' => $strain,
            'form' => $form->createView(),
            'typeOfStrain' => 'wild',
        ]);
    }

    /**
     * @Route("/parental/parents/{id}", name="strain_parental_parents")
     */
    public function parentalParentsStrainsAction(GmoStrain $gmoStrain)
    {
        $em = $this->getDoctrine()->getManager();
        $strain = $em->getRepository('AppBundle:GmoStrain')->findParents($gmoStrain);

        $array['name'] = $strain->getFullName();

        $c = 0;
        foreach ($strain->getParents() as $parent) {
            $array['children'][$c]['name'] = $parent->getFullName();

            foreach ($parent->getParents() as $parent2) {
                $array['children'][$c]['children'][]['name'] = $parent2->getFullName();
            }

            ++$c;
        }

        $response = new Response(json_encode($array));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/parental/children/{id}", name="strain_parental_children")
     */
    public function parentalChildrenStrainsAction(GmoStrain $gmoStrain)
    {
        $em = $this->getDoctrine()->getManager();
        $strain = $em->getRepository('AppBundle:GmoStrain')->findChildren($gmoStrain);

        $array['name'] = $strain->getFullName();

        $c = 0;
        foreach ($strain->getChildren() as $child) {
            $array['children'][$c]['name'] = $child->getFullName();

            foreach ($child->getChildren() as $child2) {
                $array['children'][$c]['children'][]['name'] = $child2->getFullName();
            }

            ++$c;
        }

        $response = new Response(json_encode($array));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
