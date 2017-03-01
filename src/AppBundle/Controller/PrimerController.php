<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Primer;
use AppBundle\Form\Type\PrimerEditType;
use AppBundle\Form\Type\PrimerType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class primerController.
 *
 * @Route("/primer")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class PrimerController extends Controller
{
    /**
     * @Route("/",
     *     options={"expose"=true},
     *     name="primer_index"
     * )
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction($request);

        return $this->render('primer/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
        ]);
    }

    /**
     * @Route(
     *     "/list",
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()",
     *     name="primer_index_ajax"
     * )
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function listAction(Request $request)
    {
        $query = ('' !== $request->get('q') && null !== $request->get('q')) ? $request->get('q') : null;
        $teamId = ('' !== $request->get('team') && null !== $request->get('team')) ? $request->get('team') : $this->getUser()->getFavoriteTeam()->getId();
        $page = (0 < (int) $request->get('p')) ? $request->get('p') : 1;

        $repositoryManager = $this->get('fos_elastica.manager.orm');
        $repository = $repositoryManager->getRepository('AppBundle:Primer');
        $elasticQuery = $repository->searchByNameQuery($query, $page, $teamId, $this->getUser());
        $primersList = $this->get('fos_elastica.finder.app.primer')->find($elasticQuery);
        $nbResults = $this->get('fos_elastica.index.app.primer')->count($elasticQuery);

        $nbPages = ceil($nbResults / Primer::NUM_ITEMS);

        return $this->render('primer/_list.html.twig', [
            'primersList' => $primersList,
            'query' => $query,
            'page' => $page,
            'nbPages' => $nbPages,
        ]);
    }

    /**
     * @Route("/add", name="primer_add")
     * @Security("user.isInTeam()")
     */
    public function addAction(Request $request)
    {
        $primer = new Primer();
        $form = $this->createForm(PrimerType::class, $primer)
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
            $em->persist($primer);
            $em->flush();

            $this->addFlash('success', 'The primer has been added successfully.');

            $nextAction = $form->get('saveAndAdd')->isClicked()
                ? 'primer_add'
                : 'primer_index';

            return $this->redirectToRoute($nextAction);
        }

        return $this->render('primer/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}-{slug}", name="primer_view", requirements={"id": "\d+"})
     * @Security("is_granted('PRIMER_VIEW', primer)")
     */
    public function viewAction(Primer $primer)
    {
        return $this->render('primer/view.html.twig', [
            'primer' => $primer,
        ]);
    }

    /**
     * @Route("/{id}-{slug}/edit", name="primer_edit", requirements={"id": "\d+"})
     * @Security("is_granted('PRIMER_EDIT', primer)")
     */
    public function editAction(Primer $primer, Request $request)
    {
        $form = $this->createForm(PrimerEditType::class, $primer);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The primer has been edited successfully.');

            return $this->redirectToRoute('primer_view', [
                'id' => $primer->getId(),
                'slug' => $primer->getSlug(),
            ]);
        }

        return $this->render('primer/edit.html.twig', [
            'primer' => $primer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}-{slug}/delete", name="primer_delete", requirements={"id": "\d+"})
     * @Method("POST")
     * @Security("is_granted('PRIMER_DELETE', primer)")
     */
    public function deleteAction(Primer $primer, Request $request)
    {
        // If the CSRF token is invalid, redirect user
        if (!$this->isCsrfTokenValid('primer_delete', $request->request->get('token'))) {
            $this->addFlash('warning', 'The CSRF token is invalid.');

            return $this->redirectToRoute('primer_view', [
                'id' => $primer->getId(),
                'slug' => $primer->getSlug(),
            ]);
        }

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->remove($primer);
        $entityManager->flush();

        $this->addFlash('success', 'The primer has been deleted successfully.');

        return $this->redirectToRoute('primer_index');
    }
}
