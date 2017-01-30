<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Plasmid;
use AppBundle\Form\Type\PlasmidType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Route("/", name="plasmid_index")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $plasmids = $em->getRepository('AppBundle:Plasmid')->findAllForUser($this->getUser());

        return $this->render('plasmid/index.html.twig', [
            'plasmids' => $plasmids,
        ]);
    }

    /**
     * @Route("/view/{id}", name="plasmid_view")
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
     * @Route("/edit/{id}", name="plasmid_edit")
     * @Security("is_granted('PLASMID_EDIT', plasmid)")
     */
    public function editAction(Plasmid $plasmid, Request $request)
    {
        $form = $this->createForm(PlasmidType::class, $plasmid)
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
     * @Route("/delete/{id}", name="plasmid_delete")
     * @Security("is_granted('PLASMID_DELETE', plasmid)")
     */
    public function deleteAction(Plasmid $plasmid, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($plasmid);
            $em->flush();

            $this->addFlash('success', 'The plasmid has been deleted successfully.');

            return $this->redirectToRoute('plasmid_index');
        }

        return $this->render('plasmid/delete.html.twig', [
            'plasmid' => $plasmid,
            'form' => $form->createView(),
        ]);
    }
}
