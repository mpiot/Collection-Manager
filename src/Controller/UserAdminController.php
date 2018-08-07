<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\RoleType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserAdminController extends Controller
{
    /**
     * @Route("/users", options={"expose"=true}, name="user_index")
     * @Method("GET")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction();

        return $this->render('user/admin/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
        ]);
    }

    /**
     * @Route("/users/list", options={"expose"=true}, condition="request.isXmlHttpRequest()", name="user_index_ajax")
     * @Method("GET")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function listAction()
    {
        $results = $this->get('App\Utils\IndexFilter')->filter(User::class, true, true);

        return $this->render('user/admin/_list.html.twig', [
            'results' => $results,
        ]);
    }

    /**
     * @Route("/user/roles/{id}", name="user_roles")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function rolesAction(User $user, Request $request)
    {
        $form = $this->createForm(RoleType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The user\'s roles have been successfully edited.');

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/admin/roles.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
