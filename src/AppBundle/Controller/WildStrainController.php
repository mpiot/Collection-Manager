<?php

namespace AppBundle\Controller;

use AppBundle\Entity\WildStrain;
use AppBundle\Form\Type\WildStrainType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
     * @Route("/wild/{id}", name="strain_wild_view", requirements={"id": "\d+"})
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
     * @Route("/wild/add", name="strain_wild_add", requirements={"id": "\d+"})
     * @Security("user.isTeamAdministrator() or user.isProjectMember()")
     */
    public function addWildAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $strain = new WildStrain();

        $strainNames = $em->getRepository('AppBundle:WildStrain')->findAllName($this->getUser());

        $form = $this->createForm(WildStrainType::class, $strain)
            ->add('save', SubmitType::class, [
                'label' => 'Create',
                'attr' => [
                    'data-btn-group' => 'btn-group',
                    'data-btn-position' => 'btn-first',
                ],
            ])
            ->add('saveAndAdd', SubmitType::class, [
                'label' => 'Create and Add',
                'attr' => [
                    'data-btn-group' => 'btn-group',
                    'data-btn-position' => 'btn-last',
                ],
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($strain);
            $em->flush();

            $this->addFlash('success', 'The strain has been added successfully: '.$strain->getAutoName());

            $nextAction = $form->get('saveAndAdd')->isClicked()
                ? 'strain_wild_add'
                : 'strain_index';

            return $this->redirectToRoute($nextAction);
        }

        return $this->render('strain/wild/add.html.twig', [
            'form' => $form->createView(),
            'strainNames' => $strainNames,
        ]);
    }

    /**
     * @Route("/wild/{id}/edit", name="strain_wild_edit", requirements={"id": "\d+"})
     * @ParamConverter("WildStrain", class="AppBundle:WildStrain", options={
     *      "repository_method" = "findOneWithAll"
     * })
     * @Security("is_granted('STRAIN_EDIT', strain)")
     */
    public function editWildAction(WildStrain $strain, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $strainNames = $em->getRepository('AppBundle:WildStrain')->findAllName($this->getUser());

        $form = $this->createForm(WildStrainType::class, $strain)
            ->add('edit', SubmitType::class, [
                'label' => 'Edit',
                'attr' => [
                    'data-btn-group' => 'btn-group',
                    'data-btn-position' => 'btn-first',
                ],
            ]);

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
     * @Route("/wild/{id}/delete", name="strain_wild_delete", requirements={"id": "\d+"})
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
