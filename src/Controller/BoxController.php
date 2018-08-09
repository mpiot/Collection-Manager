<?php

namespace App\Controller;

use App\Entity\Box;
use App\Entity\Group;
use App\Form\Type\BoxEditType;
use App\Form\Type\BoxImportType;
use App\Form\Type\BoxType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BoxController.
 *
 * @Route("/box")
 */
class BoxController extends Controller
{
    /**
     * @Route("/",  name="box_index", methods={"GET"})
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction($request);

        return $this->render('box/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
            'queryGroup' => $request->get('group'),
        ]);
    }

    /**
     * @Route("/list",  condition="request.isXmlHttpRequest()", name="box_index_ajax", methods={"GET"})
     */
    public function listAction(Request $request)
    {
        $results = $this->get('App\Utils\IndexFilter')->filter(Box::class, true, true, [Group::class]);

        return $this->render('box/_list.html.twig', [
            'results' => $results,
        ]);
    }

    /**
     * @Route("/add", name="box_add", methods={"GET", "POST"})
     * @Security("user.isInGroup()")
     */
    public function addAction(Request $request)
    {
        $box = new Box();
        $form = $this->createForm(BoxType::class, $box)
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
            $em->persist($box);
            $em->flush();

            $this->addFlash('success', 'The box has been added successfully.');

            $nextAction = $form->get('saveAndAdd')->isClicked()
                ? 'box_add'
                : 'box_index';

            return $this->redirectToRoute($nextAction);
        }

        return $this->render('box/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}-{slug}", name="box_view", requirements={"id": "\d+"}, methods={"GET"})
     * @Entity("box", expr="repository.findOneWithStrains(id)")
     * @Security("box.getGroup().isMember(user)")
     */
    public function viewAction(Box $box)
    {
        $tubesList = $box->getTubes()->toArray();
        $tubes = [];

        foreach ($tubesList as $tube) {
            $tubes[$tube->getCell()] = $tube;
        }

        $deleteForm = $this->createDeleteForm($box);

        return $this->render('box/view.html.twig', [
            'box' => $box,
            'tubes' => $tubes,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}-{slug}/edit", name="box_edit", methods={"GET", "POST"})
     * @Security("box.isAuthor(user) or box.getGroup().isAdministrator(user)")
     */
    public function editAction(Box $box, Request $request)
    {
        $form = $this->createForm(BoxEditType::class, $box);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The box has been edited successfully.');

            return $this->redirectToRoute('box_index');
        }

        return $this->render('box/edit.html.twig', [
            'box' => $box,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}-{slug}/delete", name="box_delete", methods={"DELETE"})
     * @Security("box.isAuthor(user) or box.getGroup().isAdministrator(user)")
     */
    public function deleteAction(Box $box, Request $request)
    {
        // Check if the box is Empty of not
        if (!$box->isEmpty()) {
            $this->addFlash('warning', 'The box contains tubes, it cannot be removed.');

            return $this->redirectToRoute('box_view', ['id' => $box->getId(), 'slug' => $box->getSlug()]);
        }

        $form = $this->createDeleteForm($box);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($box);
            $em->flush();
        }

        $this->addFlash('success', 'The box has been deleted successfully.');

        return $this->redirectToRoute('box_index');
    }

    /**
     * Creates a form to delete a box entity.
     *
     * @param Box $box The box entity
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createDeleteForm(Box $box)
    {
        return $this->createFormBuilder(null, ['attr' => ['data-confirmation' => true]])
            ->setAction($this->generateUrl('box_delete', ['id' => $box->getId(), 'slug' => $box->getSlug()]))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }

    /**
     * @Route("/{id}-{slug}/export", name="box_export", methods={"GET"})
     * @Entity("box", expr="repository.findForCSVExport(id)")
     * @Security("box.getGroup().isMember(user)")
     */
    public function exportAction(Box $box)
    {
        $fileName = $box->getGroup()->getName().'-'.$box->getName();

        $response = new StreamedResponse();
        $response->setCallback(function () use ($box) {
            $this->get('App\Utils\CSVExporter')->exportBox($box);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$fileName.'.csv"');
        $response->send();
    }

    /**
     * @Route("/{id}-{slug}/import", name="box_import", methods={"GET", "POST"})
     * @Security("box.getGroup().isMember(user)")
     */
    public function importAction(Box $box, Request $request)
    {
        $form = $this->createForm(BoxImportType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('App\Utils\CSVImporter')->importBox($box, $form);

            if ($form->isValid()) {
                $this->addFlash('success', 'Strains has been successfully imported.');

                return $this->redirectToRoute('box_view', [
                    'id' => $box->getId(),
                    'slug' => $box->getSlug(),
                ]);
            }
        }

        return $this->render('box/import.html.twig', [
            'form' => $form->createView(),
            'box' => $box,
        ]);
    }
}
