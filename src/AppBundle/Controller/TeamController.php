<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Team;
use AppBundle\Form\TeamType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TeamController.
 *
 * @Route("team")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class TeamController extends Controller
{
    /**
     * @Route("/", name="team_index")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $teams = $em->getRepository('AppBundle:Team')->findAllWithMembers();

        return $this->render('team/index.html.twig', array(
            'teams' => $teams,
        ));
    }

    /**
     * @Route("/view/{id}", name="team_view")
     * @ParamConverter("team", class="AppBundle:Team", options={
     *      "repository_method" = "findOneWithMembers"
     * })
     */
    public function viewAction(Team $team)
    {
        return $this->render('team/view.html.twig', array(
            'team' => $team,
        ));
    }

    /**
     * @Route("/add", name="team_add")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($team);
            $em->flush();

            $this->addFlash('success', 'The team has been added successfully.');

            return $this->redirectToRoute('team_view', array('id' => $team->getId()));
        }

        return $this->render('team/add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/edit/{id}", name="team_edit")
     * @ParamConverter("team", class="AppBundle:Team", options={
     *      "repository_method" = "findOneWithMembers"
     * })
     * @Security("user.isAdministratorOf(team) or is_granted('ROLE_ADMIN')")
     */
    public function editAction(Team $team, Request $request)
    {
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The team has been edited successfully.');

            return $this->redirectToRoute('team_view', array('id' => $team->getId()));
        }

        return $this->render('team/edit.html.twig', array(
            'form' => $form->createView(),
            'team' => $team,
        ));
    }

    /**
     * @Route("/delete/{id}", name="team_delete")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteAction(Team $team, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($team);
            $em->flush();

            $this->addFlash('success', 'The team has been deleted successfully.');

            return $this->redirectToRoute('team_index');
        }

        return $this->render('team/delete.html.twig', array(
            'form' => $form->createView(),
            'team' => $team,
        ));
    }
}
