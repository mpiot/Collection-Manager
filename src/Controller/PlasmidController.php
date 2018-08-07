<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Plasmid;
use App\Form\Type\PlasmidEditType;
use App\Form\Type\PlasmidType;
use App\Utils\PlasmidGenBank;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class plasmidController.
 *
 * @Route("/plasmid")
 */
class PlasmidController extends Controller
{
    /**
     * @Route("/", options={"expose"=true}, name="plasmid_index", methods={"GET"})
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction();

        return $this->render('plasmid/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
            'queryGroup' => $request->get('group'),
        ]);
    }

    /**
     * @Route("/list", options={"expose"=true}, condition="request.isXmlHttpRequest()", name="plasmid_index_ajax", methods={"GET"})
     */
    public function listAction()
    {
        $results = $this->get('App\Utils\IndexFilter')->filter(Plasmid::class, true, true, [Group::class]);

        return $this->render('plasmid/_list.html.twig', [
            'results' => $results,
        ]);
    }

    /**
     * @Route("/add", name="plasmid_add", methods={"GET", "POST"})
     * @Security("user.isInGroup()")
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
     * @Route("/{id}-{slug}", name="plasmid_view", requirements={"id": "\d+"}, methods={"GET"})
     * @Entity("plasmid", class="App:Plasmid", expr="repository.findOneWithAll(id)")
     * @Security("plasmid.getGroup().isMember(user)")
     */
    public function viewAction(Plasmid $plasmid)
    {
        $gbk = new PlasmidGenBank($plasmid);
        $deleteForm = $this->createDeleteForm($plasmid);

        return $this->render('plasmid/view.html.twig', [
            'plasmid' => $plasmid,
            'gbkFile' => $gbk->getFile(),
            'gbk' => $gbk->getArray(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}-{slug}/edit", name="plasmid_edit", requirements={"id": "\d+"}, methods={"GET", "POST"})
     * @Security("plasmid.isAuthor(user) or plasmid.getGroup().isAdministrator(user)")
     */
    public function editAction(Plasmid $plasmid, Request $request)
    {
        $form = $this->createForm(PlasmidEditType::class, $plasmid)
            ->add('save', SubmitType::class, [
                'label' => 'Save changes',
                'attr' => [
                    'data-btn-group' => 'btn-group',
                    'data-btn-position' => 'btn-first',
                ],
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
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
     * @Route("/{id}-{slug}", name="plasmid_delete", methods={"DELETE"})
     * @Security("plasmid.isAuthor(user) or plasmid.getGroup().isAdministrator(user)")
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

        $form = $this->createDeleteForm($plasmid);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($plasmid);
            $em->flush();
        }

        $this->addFlash('success', 'The plasmid has been deleted successfully.');

        return $this->redirectToRoute('plasmid_index');
    }

    /**
     * Creates a form to delete a plasmid entity.
     *
     * @param Plasmid $plasmid The plasmid entity
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createDeleteForm(Plasmid $plasmid)
    {
        return $this->createFormBuilder(null, ['attr' => ['data-confirmation' => true]])
            ->setAction($this->generateUrl('plasmid_delete', ['id' => $plasmid->getId(), 'slug' => $plasmid->getSlug()]))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }

    /**
     * @Route("/{id}-{slug}/download", name="plasmid_download", methods={"GET"})
     * @Security("plasmid.getGroup().isMember(user)")
     */
    public function downloadAction(Plasmid $plasmid)
    {
        if (null === $plasmid->getGenBankName()) {
            throw $this->createNotFoundException("This file doesn't exists.");
        }

        // Get the absolute path of the file and the path for X-Accel-Redirect
        $filePath = $this->get('vich_uploader.storage')->resolvePath($plasmid, 'genBankFile');
        $xSendFilePath = $this->get('vich_uploader.storage')->resolveUri($plasmid, 'genBankFile');
        $fileName = $plasmid->getAutoName().'_'.$plasmid->getSlug().'.'.pathinfo($filePath)['extension'];

        // Return a Binary Response
        BinaryFileResponse::trustXSendfileTypeHeader();
        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        );
        $response->headers->set('X-Accel-Redirect', $xSendFilePath);

        return $response;
    }
}
