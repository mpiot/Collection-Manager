<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Type;
use AppBundle\Form\Type\TypeEditType;
use AppBundle\Form\Type\TypeType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        $teamId = ('' !== $request->get('team') && null !== $request->get('team')) ? $request->get('team') : $this->getUser()->getFavoriteTeam()->getId();
        $page = (0 < (int) $request->get('p')) ? $request->get('p') : 1;

        $repositoryManager = $this->get('fos_elastica.manager.orm');
        $repository = $repositoryManager->getRepository('AppBundle:Type');
        $elasticQuery = $repository->searchByNameQuery($query, $page, $teamId, $this->getUser());
        $nbResults = $this->get('fos_elastica.index.app.type')->count($elasticQuery);
        $finder = $this->get('fos_elastica.finder.app.type');
        $typesList = $finder->find($elasticQuery);

        $nbPages = ceil($nbResults / Type::NUM_ITEMS);

        return $this->render('type/_list.html.twig', [
            'typesList' => $typesList,
            'query' => $query,
            'page' => $page,
            'nbPages' => $nbPages,
        ]);
    }

    /**
     * @Route("/add", name="type_add")
     * @Security("user.isTeamAdministrator() or user.isProjectAdministrator()")
     */
    public function addAction(Request $request)
    {
        $type = new Type();
        $form = $this->createForm(TypeType::class, $type)
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
            $em->persist($type);
            $em->flush();

            $this->addFlash('success', 'The type has been added successfully.');

            $nextAction = $form->get('saveAndAdd')->isClicked()
                ? 'type_add'
                : 'type_index';

            return $this->redirectToRoute($nextAction);
        }

        return $this->render('type/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/embdedAdd", name="type_embded_add", condition="request.isXmlHttpRequest()")
     * @Security("user.isTeamAdministrator() or user.isProjectAdministrator()")
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
                'name' => $type->getName(),
            ]);
        }

        return $this->render('type/embdedAdd.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="type_edit")
     * @Security("is_granted('TYPE_EDIT', type)")
     */
    public function editAction(Type $type, Request $request)
    {
        $form = $this->createForm(TypeEditType::class, $type);

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
     * @Route("/{id}/delete", name="type_delete")
     * @Method("POST")
     * @Security("is_granted('TYPE_DELETE', type)")
     */
    public function deleteAction(Type $type, Request $request)
    {
        // If the type is used by strains, redirect user
        if (!$type->getGmoStrains()->isEmpty() || !$type->getWildStrains()->isEmpty()) {
            $this->addFlash('warning', 'The type cannot be deleted, it\'s used in strain(s).');

            return $this->redirectToRoute('type_index');
        }

        // If the CSRF token is invalid, redirect user
        if (!$this->isCsrfTokenValid('type_delete', $request->request->get('token'))) {
            $this->addFlash('warning', 'The CSRF token is invalid.');

            return $this->redirectToRoute('type_index');
        }

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->remove($type);
        $entityManager->flush();

        $this->addFlash('success', 'The type has been deleted successfully.');

        return $this->redirectToRoute('type_index');
    }
}
