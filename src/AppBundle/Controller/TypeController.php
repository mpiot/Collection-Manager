<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Type;
use AppBundle\Form\Type\TypeType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class typeController.
 *
 * @Route("/type")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class TypeController extends Controller
{
    const HIT_PER_PAGE = 10;

    /**
     * @Route(
     *     "/",
     *     options={"expose"=true},
     *     name="type_index"
     * )
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction($request);

        return $this->render('type/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
        ]);
    }

    /**
     * @Route(
     *     "/list",
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()",
     *     name="type_index_ajax"
     * )
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function listAction(Request $request)
    {
        $query = ('' !== $request->get('q') && null !== $request->get('q')) ? $request->get('q') : null;
        $page = (0 < (int) $request->get('p')) ? $request->get('p') : 1 ;

        $repositoryManager = $this->container->get('fos_elastica.manager.orm');
        $repository = $repositoryManager->getRepository('AppBundle:Type');
        $typesList = $repository->findByName($query, $page, self::HIT_PER_PAGE);

        $nbPages = ceil($typesList->getNbResults() / self::HIT_PER_PAGE);

        return $this->render('type/list.html.twig', [
            'typesList' => $typesList,
            'query'       => $query,
            'page'        => $page,
            'nbPages'     => $nbPages,
        ]);
    }

    /**
     * @Route("/add", name="type_add")
     * @Security("user.isTeamAdministrator() or user.isProjectAdministrator() or is_granted('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        $type = new Type();
        $form = $this->createForm(TypeType::class, $type);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($type);
            $em->flush();

            $this->addFlash('success', 'The type has been added successfully.');

            return $this->redirectToRoute('type_index');
        }

        return $this->render('type/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/embdedAdd", name="type_embded_add", condition="request.isXmlHttpRequest()")
     * @Security("user.isTeamAdministrator() or user.isProjectAdministrator() or is_granted('ROLE_ADMIN')")
     */
    public function embdedAddAction(Request $request)
    {
        $type = new Type();
        $form = $this->createForm(TypeType::class, $type, [
            'action' => $this->generateUrl('type_embded_add'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($type);
            $em->flush();

            // return a json response with the new type
            return new JsonResponse([
                'success' => true,
                'id' => $type->getId(),
                'name'=> $type->getName(),
            ]);
        }

        return $this->render('type/embdedAdd.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="type_edit")
     * @Security("user.isTeamAdministrator() or user.isProjectAdministrator() or is_granted('ROLE_ADMIN')")
     */
    public function editAction(Type $type, Request $request)
    {
        $form = $this->createForm(TypeType::class, $type);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The type has been edited successfully.');

            return $this->redirectToRoute('type_index');
        }

        return $this->render('type/edit.html.twig', [
            'type' => $type,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="type_delete")
     * @Security("user.isTeamAdministrator() or user.isProjectAdministrator() or is_granted('ROLE_ADMIN')")
     */
    public function deleteAction(Type $type, Request $request)
    {
        // Check if the type is used in strains, else redirect user
        if (!$type->getGmoStrains()->isEmpty() || !$type->getWildStrains()->isEmpty()) {
            $this->addFlash('warning', 'The type cannot be deleted, it\'s used in strain(s).');

            return $this->redirectToRoute('type_index');
        }

        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($type);
            $em->flush();

            $this->addFlash('success', 'The type has been deleted successfully.');

            return $this->redirectToRoute('type_index');
        }

        return $this->render('type/delete.html.twig', [
            'type' => $type,
            'form' => $form->createView(),
        ]);
    }
}
