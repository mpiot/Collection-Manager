<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Primer;
use App\Form\Type\PrimerEditType;
use App\Form\Type\PrimerType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class primer controller.
 *
 * @Route("/primer")
 */
class PrimerController extends Controller
{
    /**
     * @Route("/",  name="primer_index", methods={"GET"})
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction();

        return $this->render('primer/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
            'queryGroup' => $request->get('group'),
        ]);
    }

    /**
     * @Route("/list",  condition="request.isXmlHttpRequest()", name="primer_index_ajax", methods={"GET"})
     */
    public function listAction()
    {
        $results = $this->get('App\Utils\IndexFilter')->filter(Primer::class, true, true, [Group::class]);

        return $this->render('primer/_list.html.twig', [
            'results' => $results,
        ]);
    }

    /**
     * @Route("/add", name="primer_add", methods={"GET", "POST"})
     * @Security("user.isInGroup()")
     */
    public function addAction(Request $request)
    {
        $primer = new Primer();
        $form = $this->createForm(PrimerType::class, $primer)
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
     * @Route("/{id}-{slug}", name="primer_view", methods={"GET"})
     * @Security("primer.getGroup().isMember(user)")
     */
    public function viewAction(Primer $primer)
    {
        $deleteForm = $this->createDeleteForm($primer);

        return $this->render('primer/view.html.twig', [
            'primer' => $primer,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}-{slug}/edit", name="primer_edit", requirements={"id": "\d+"}, methods={"GET", "POST"})
     * @Security("primer.isAuthor(user) or primer.getGroup().isAdministrator(user)")
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
     * @Route("/{id}-{slug}", name="primer_delete", methods={"DELETE"})
     * @Security("primer.isAuthor(user) or primer.getGroup().isAdministrator(user)")
     */
    public function deleteAction(Primer $primer, Request $request)
    {
        $form = $this->createDeleteForm($primer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($primer);
            $em->flush();
        }

        $this->addFlash('success', 'The primer has been deleted successfully.');

        return $this->redirectToRoute('primer_index');
    }

    /**
     * Creates a form to delete a primer entity.
     *
     * @param Primer $primer The primer entity
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createDeleteForm(Primer $primer)
    {
        return $this->createFormBuilder(null, ['attr' => ['data-confirmation' => true]])
            ->setAction($this->generateUrl('primer_delete', ['id' => $primer->getId(), 'slug' => $primer->getSlug()]))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}
