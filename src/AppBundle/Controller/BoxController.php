<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Box;
use AppBundle\Entity\Project;
use AppBundle\Form\Type\BoxEditType;
use AppBundle\Form\Type\BoxType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $boxes = $em->getRepository('AppBundle:Box')->findAllAuthorizedForCurrentUserWithType($this->getUser());

        return $this->render('box/index.html.twig', [
            'boxes' => $boxes,
        ]);
    }

    /**
     * @Route("/view/{id}", name="box_view")
     * @ParamConverter("box", class="AppBundle:Box", options={
     *     "repository_method" = "findOneWithProjectTypeTubesStrains"
     * })
     * @Security("is_granted('BOX_VIEW', box)")
     */
    public function viewAction(Box $box)
    {
        $tubesList = $box->getTubes()->toArray();
        $tubes = [];

        foreach ($tubesList as $tube) {
            $tubes[$tube->getCell()] = $tube;
        }

        return $this->render('box/view.html.twig', [
            'box' => $box,
            'tubes' => $tubes,
        ]);
    }

    /**
     * @Route("/add", name="box_add")
     * @Route("/add/{id}", name="box_add_4_project")
     * @ParamConverter("project", class="AppBundle:Project")
     * @Security("user.isTeamAdministrator() or user.isProjectAdministrator() or user.isProjectMember() or is_granted('ROLE_ADMIN')")
     */
    public function addAction(Request $request, Project $project = null)
    {
        $box = new Box();
        $box->setProject($project);
        $form = $this->createForm(BoxType::class, $box)
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
            $em = $this->getDoctrine()->getManager();
            $em->persist($box);
            $em->flush();

            $this->addFlash('success', 'The box has been added successfully.');

            $nextAction = $form->get('saveAndAdd')->isClicked()
                ? 'box_add'
                : 'box_index';

            return $this->redirectToRoute($nextAction);
        }

        return $this->render('box/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="box_edit")
     * @Security("is_granted('BOX_EDIT', box)")
     */
    public function editAction(Box $box, Request $request)
    {
        $form = $this->createForm(BoxEditType::class, $box);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The box has been edited successfully.');

            return $this->redirectToRoute('box_index');
        }

        return $this->render('box/edit.html.twig', [
            'box' => $box,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="box_delete")
     * @Security("is_granted('BOX_DELETE', box)")
     */
    public function deleteAction(Box $box, Request $request)
    {
        if ($box->isDeleted()) {
            $this->addFlash('warning', 'The box has been already deleted.');

            return $this->redirectToRoute('box_index');
        }

        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

        return $this->render('box/delete.html.twig', [
            'box' => $box,
            'form' => $form->createView(),
        ]);
    }
}
