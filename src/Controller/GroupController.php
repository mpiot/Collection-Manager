<?php

namespace App\Controller;

use App\Entity\Group;
use App\Form\Type\GroupType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GroupController.
 *
 * @Route("group")
 */
class GroupController extends Controller
{
    /**
     * @Route("/", name="group_index")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $groups = $em->getRepository('App:Group')->findAllWithMembers();

        return $this->render('group/index.html.twig', [
            'groups' => $groups,
        ]);
    }

    /**
     * @Route("/add", name="group_add")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function addAction(Request $request)
    {
        $group = new Group();
        $form = $this->createForm(GroupType::class, $group)
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'attr' => [
                    'data-btn-group' => 'btn-group',
                    'data-btn-position' => 'btn-first',
                ],
            ])
            ->add('saveAndAdd', SubmitType::class, [
                'label' => 'Save & Add',
                'attr' => [
                    'data-btn-group' => 'btn-group',
                    'data-btn-position' => 'btn-last',
                ],
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($group);
            $em->flush();

            $this->addFlash('success', 'The group has been added successfully.');

            if ($form->get('saveAndAdd')->isClicked()) {
                return $this->redirectToRoute('group_add');
            }

            return $this->redirectToRoute('group_view', ['slug' => $group->getSlug()]);
        }

        return $this->render('group/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="group_view")
     * @Method("GET")
     * @Entity("group", class="App:Group", expr="repository.findOneWithMembers(slug)")
     * @Security("is_granted('ROLE_USER')")
     */
    public function viewAction(Group $group)
    {
        return $this->render('group/view.html.twig', [
            'group' => $group,
        ]);
    }

    /**
     * @Route("/{slug}/edit", name="group_edit")
     * @Method({"GET", "POST"})
     * @Entity("group", class="App:Group", expr="repository.findOneWithMembers(slug)")
     * @Security("group.isAdministrator(user) or is_granted('ROLE_SUPER_ADMIN')")
     */
    public function editAction(Group $group, Request $request)
    {
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The group has been edited successfully.');

            return $this->redirectToRoute('group_view', ['slug' => $group->getSlug()]);
        }

        return $this->render('group/edit.html.twig', [
            'form' => $form->createView(),
            'group' => $group,
        ]);
    }

    /**
     * @Route("/{slug}/delete", name="group_delete")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function deleteAction(Group $group, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($group);
            $em->flush();

            $this->addFlash('success', 'The group has been deleted successfully.');

            return $this->redirectToRoute('group_index');
        }

        return $this->render('group/delete.html.twig', [
            'form' => $form->createView(),
            'group' => $group,
        ]);
    }

    /**
     * @Route("/{slug}/favorite", name="group_favorite")
     * @Method("GET")
     * @Security("group.isMember(user)")
     */
    public function favoriteAction(Group $group)
    {
        // The member need to be in the group, to set this group as his favorite
        if (!$this->getUser()->hasGroup($group)) {
            $this->addFlash('warning', 'You\'re not in this group.');

            return $this->redirectToRoute('homepage');
        }

        $em = $this->getDoctrine()->getManager();
        $this->getUser()->setFavoriteGroup($group);
        $em->flush();

        $this->addFlash('success', 'The group has been set as favorite successfully.');

        return $this->redirectToRoute('homepage');
    }
}
