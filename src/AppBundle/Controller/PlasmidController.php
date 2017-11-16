<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Plasmid;
use AppBundle\Entity\Team;
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
 */
class PlasmidController extends Controller
{
    /**
     * @Route("/", options={"expose"=true}, name="plasmid_index")
     * @Security("user.isInTeam()")
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction();

        return $this->render('plasmid/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
        ]);
    }

    /**
     * @Route("/list", options={"expose"=true}, condition="request.isXmlHttpRequest()", name="plasmid_index_ajax")
     * @Security("user.isInTeam()")
     */
    public function listAction()
    {
        $results = $this->get('AppBundle\Utils\IndexFilter')->filter(Plasmid::class, true, true, [Team::class]);

        return $this->render('plasmid/_list.html.twig', [
            'results' => $results,
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
     * @Route("/{id}-{slug}", name="plasmid_view", requirements={"id": "\d+"})
     * @ParamConverter("plasmid", options={"repository_method" = "findOneWithAll"})
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
     * @Route("/{id}-{slug}/edit", name="plasmid_edit", requirements={"id": "\d+"})
     * @Security("is_granted('PLASMID_EDIT', plasmid)")
     */
    public function editAction(Plasmid $plasmid, Request $request)
    {
        $form = $this->createForm(PlasmidEditType::class, $plasmid)
            ->add('save', SubmitType::class, [
                'label' => 'Save changes',
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

            return $this->redirectToRoute('plasmid_view', [
                'id' => $plasmid->getId(),
                'slug' => $plasmid->getSlug(),
            ]);
        }

        return $this->render('plasmid/edit.html.twig', [
            'plasmid' => $plasmid,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}-{slug}/delete", name="plasmid_delete", requirements={"id": "\d+"})
     * @Method("POST")
     * @Security("is_granted('PLASMID_DELETE', plasmid)")
     */
    public function deleteAction(Plasmid $plasmid, Request $request)
    {
        // If the plasmid is used by strains, redirect user
        if (!$plasmid->getStrains()->isEmpty()) {
            $this->addFlash('warning', 'The plasmid cannot be deleted, it\'s used in strain(s).');

            return $this->redirectToRoute('plasmid_view', [
                'id' => $plasmid->getId(),
                'slug' => $plasmid->getSlug(),
            ]);
        }

        // If the CSRF token is invalid, redirect user
        if (!$this->isCsrfTokenValid('plasmid_delete', $request->request->get('token'))) {
            $this->addFlash('warning', 'The CSRF token is invalid.');

            return $this->redirectToRoute('plasmid_view', [
                'id' => $plasmid->getId(),
                'slug' => $plasmid->getSlug(),
            ]);
        }

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->remove($plasmid);
        $entityManager->flush();

        $this->addFlash('success', 'The plasmid has been deleted successfully.');

        return $this->redirectToRoute('plasmid_index');
    }
}
