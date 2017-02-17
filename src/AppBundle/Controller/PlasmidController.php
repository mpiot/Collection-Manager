<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Plasmid;
use AppBundle\Form\Type\PlasmidEditType;
use AppBundle\Form\Type\PlasmidType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Utils\PlasmidGenBank;

/**
 * Class plasmidController.
 *
 * @Route("/plasmid")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class PlasmidController extends Controller
{
    /**
     * @Route("/",
     *     options={"expose"=true},
     *     name="plasmid_index"
     * )
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction($request);

        return $this->render('plasmid/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
        ]);
    }

    /**
     * @Route(
     *     "/list",
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()",
     *     name="plasmid_index_ajax"
     * )
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function listAction(Request $request)
    {
        $query = ('' !== $request->get('q') && null !== $request->get('q')) ? $request->get('q') : null;
        $teamId = ('' !== $request->get('team') && null !== $request->get('team')) ? $request->get('team') : $this->getUser()->getFavoriteTeam()->getId();
        $page = (0 < (int) $request->get('p')) ? $request->get('p') : 1;

        $repositoryManager = $this->get('fos_elastica.manager.orm');
        $repository = $repositoryManager->getRepository('AppBundle:Plasmid');
        $elasticQuery = $repository->searchByNameQuery($query, $page, $teamId, $this->getUser());
        $nbResults = $this->get('fos_elastica.index.app.plasmid')->count($elasticQuery);
        $finder = $this->get('fos_elastica.finder.app.plasmid');
        $plasmidsList = $finder->find($elasticQuery);

        $nbPages = ceil($nbResults / Plasmid::NUM_ITEMS);

        return $this->render('plasmid/_list.html.twig', [
            'plasmidsList' => $plasmidsList,
            'query' => $query,
            'page' => $page,
            'nbPages' => $nbPages,
        ]);
    }

    /**
     * @Route("/{id}", name="plasmid_view", requirements={"id": "\d+"})
     * @ParamConverter("plasmid", class="AppBundle:Plasmid", options={
     *     "repository_method" = "findOneWithAll"
     * })
     * @Security("is_granted('PLASMID_VIEW', plasmid)")
     */
    public function viewAction(Plasmid $plasmid)
    {
        $gbk = new PlasmidGenBank($plasmid);

        return $this->render('plasmid/view.html.twig', [
            'plasmid' => $plasmid,
            'gbkFile' => $gbk->getFile(),
            'gbk' => $gbk->getArray(),
        ]);
    }

    /**
     * @Route("/add", name="plasmid_add")
     * @Security("user.isInTeam()")
     */
    public function addAction(Request $request)
    {
        $plasmid = new Plasmid();
        $form = $this->createForm(PlasmidType::class, $plasmid)
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

            if ((0 === $form->get('addGenBankFile')->getData()) && (null !== $plasmid->getGenBankFile())) {
                $em->persist($plasmid->getGenBankFile());
            }

            $em->persist($plasmid);
            $em->flush();

            $this->addFlash('success', 'The plasmid has been added successfully.');

            $nextAction = $form->get('saveAndAdd')->isClicked()
                ? 'plasmid_add'
                : 'plasmid_index';

            return $this->redirectToRoute($nextAction);
        }

        return $this->render('plasmid/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="plasmid_edit")
     * @Security("is_granted('PLASMID_EDIT', plasmid)")
     */
    public function editAction(Plasmid $plasmid, Request $request)
    {
        $form = $this->createForm(PlasmidEditType::class, $plasmid)
            ->add('save', SubmitType::class, [
                'label' => 'Edit',
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ((0 === $form->get('addGenBankFile')->getData()) && (null !== $plasmid->getGenBankFile())) {
                $em->remove($plasmid->getGenBankFile());
                $plasmid->setGenBankFile(null);
            }

            $em->flush();

            $this->addFlash('success', 'The plasmid has been successfully edited.');

            return $this->redirectToRoute('plasmid_index');
        }

        return $this->render('plasmid/edit.html.twig', [
            'plasmid' => $plasmid,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="plasmid_delete")
     * @Method("POST")
     * @Security("is_granted('PLASMID_DELETE', plasmid)")
     */
    public function deleteAction(Plasmid $plasmid, Request $request)
    {
        // If the plasmid is used by strains, redirect user
        if (!$plasmid->getStrains()->isEmpty()) {
            $this->addFlash('warning', 'The plasmid cannot be deleted, it\'s used in strain(s).');

            return $this->redirectToRoute('plasmid_index');
        }

        // If the CSRF token is invalid, redirect user
        if (!$this->isCsrfTokenValid('plasmid_delete', $request->request->get('token'))) {
            $this->addFlash('warning', 'The CSRF token is invalid.');

            return $this->redirectToRoute('plasmid_index');
        }

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->remove($plasmid);
        $entityManager->flush();

        $this->addFlash('success', 'The plasmid has been deleted successfully.');

        return $this->redirectToRoute('plasmid_index');
    }
}
