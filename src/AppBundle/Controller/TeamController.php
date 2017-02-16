<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Team;
use AppBundle\Form\Type\TeamType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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

        return $this->render('team/index.html.twig', [
            'teams' => $teams,
        ]);
    }

    /**
     * @Route("/{id}", name="team_view", requirements={"id": "\d+"})
     * @ParamConverter("team", class="AppBundle:Team", options={
     *      "repository_method" = "findOneWithMembers"
     * })
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function viewAction(Team $team)
    {
        return $this->render('team/view.html.twig', [
            'team' => $team,
        ]);
    }

    /**
     * @Route("/add", name="team_add")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team)
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
            $em->persist($team);
            $em->flush();

            $this->addFlash('success', 'The team has been added successfully.');

            if ($form->get('saveAndAdd')->isClicked()) {
                return $this->redirectToRoute('team_add');
            } else {
                return $this->redirectToRoute('team_view', ['id' => $team->getId()]);
            }
        }

        return $this->render('team/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="team_edit")
     * @ParamConverter("team", class="AppBundle:Team", options={
     *      "repository_method" = "findOneWithMembers"
     * })
     * @Security("user.isAdministratorOf(team) or is_granted('ROLE_ADMIN')")
     */
    public function editAction(Team $team, Request $request)
    {
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The team has been edited successfully.');

            return $this->redirectToRoute('team_view', ['id' => $team->getId()]);
        }

        return $this->render('team/edit.html.twig', [
            'form' => $form->createView(),
            'team' => $team,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="team_delete")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteAction(Team $team, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($team);
            $em->flush();

            $this->addFlash('success', 'The team has been deleted successfully.');

            return $this->redirectToRoute('team_index');
        }

        return $this->render('team/delete.html.twig', [
            'form' => $form->createView(),
            'team' => $team,
        ]);
    }

    /**
     * @Route("/{id}/favorite", name="team_favorite")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function favoriteAction(Team $team)
    {
        // The member need to be in the team, to set this team as his favorite
        if (!$this->getUser()->hasTeam($team)) {
            $this->addFlash('warning', 'You\'re not in this team.');

            return $this->redirectToRoute('homepage');
        }

        $em = $this->getDoctrine()->getManager();
        $this->getUser()->setFavoriteTeam($team);
        $em->flush();

        $this->addFlash('success', 'The team has been set as favorite successfully.');

        return $this->redirectToRoute('homepage');
    }
}
