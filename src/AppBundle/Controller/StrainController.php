<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Strain;
use AppBundle\Entity\Group;
use AppBundle\Form\Type\StrainGmoType;
use AppBundle\Form\Type\StrainWildType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class StrainController.
 *
 * @Route("/strain")
 */
class StrainController extends Controller
{
    /**
     * @Route("/", options={"expose"=true}, name="strain_index")
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction();

        return $this->render('strain/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
            'queryGroup' => $request->get('group'),
        ]);
    }

    /**
     * @Route("/list", options={"expose"=true}, condition="request.isXmlHttpRequest()", name="strain_index_ajax")
     */
    public function listAction()
    {
        $results = $this->get('AppBundle\Utils\IndexFilter')->filter(Strain::class, true, true, [Group::class]);

        return $this->render('strain/_list.html.twig', [
            'results' => $results,
        ]);
    }

    /**
     * @Route("/add/gmo", name="strain_add_gmo")
     * @Route("/add/wild", name="strain_add_wild")
     * @Route("/add/{id}-{slug}", name="strain_add_from_model", requirements={"id": "\d+"})
     * @Security("user.isInGroup()")
     */
    public function addAction(Request $request, Strain $strainModel = null)
    {
        $strain = new Strain();
        if ('strain_add_wild' === $request->get('_route')) {
            $strain->setDiscriminator('wild');
            $formType = StrainWildType::class;
        } else {
            $strain->setDiscriminator('gmo');
            $formType = StrainGmoType::class;
        }

        if ($strainModel) {
            $strain = clone $strainModel;
            $formType = 'gmo' === $strain->getDiscriminator() ? StrainGmoType::class : StrainWildType::class;
        }

        $form = $this->createForm($formType, $strain)
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
                ],
            ])
            ->add('saveAndCopy', SubmitType::class, [
                'label' => 'Save & Copy',
                'attr' => [
                    'data-btn-group' => 'btn-group',
                    'data-btn-position' => 'btn-last',
                ],
            ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($strain);
            $em->flush();

            $this->addFlash('success', 'The strain has been added successfully: '.$strain->getAutoName());

            if ($form->get('saveAndAdd')->isClicked()) {
                return $this->redirectToRoute('strain_add_'.$strain->getDiscriminator());
            } elseif ($form->get('saveAndCopy')->isClicked()) {
                return $this->redirectToRoute('strain_add_from_model', ['id' => $strain->getId(), 'slug' => $strain->getSlug()]);
            } else {
                return $this->redirectToRoute('strain_view', ['id' => $strain->getId(), 'slug' => $strain->getSlug()]);
            }
        }

        return $this->render('strain/add.html.twig', [
            'form' => $form->createView(),
            'strain' => $strain,
        ]);
    }

    /**
     * @Route("/{id}-{slug}", name="strain_view", requirements={"id": "\d+"})
     * @ParamConverter("strain", options={"repository_method" = "findOneBySlug"})
     * @Security("strain.getGroup().isMember(user)")
     */
    public function viewAction(Strain $strain)
    {
        $deleteForm = $this->createDeleteForm($strain);

        return $this->render('strain/view.html.twig', [
            'strain' => $strain,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}-{slug}/edit", name="strain_edit", requirements={"id": "\d+"})
     * @ParamConverter("strain", options={"repository_method" = "findOneBySlug"})
     * @Security("strain.isAuthor(user) or strain.getGroup().isAdministrator(user)")
     */
    public function editAction(Strain $strain, Request $request)
    {
        if ('gmo' === $strain->getDiscriminator()) {
            $form = $this->createForm(StrainGmoType::class, $strain);
        } else {
            $form = $this->createForm(StrainWildType::class, $strain);
        }

        $form->add('edit', SubmitType::class, [
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

            $this->container->get('fos_elastica.object_persister.app.strain')->replaceOne($strain);

            $this->addFlash('success', 'The strain has been edited successfully.');

            return $this->redirectToRoute('strain_view', [
                'id' => $strain->getId(),
                'slug' => $strain->getSlug(),
            ]);
        }

        return $this->render('strain/edit.html.twig', [
            'form' => $form->createView(),
            'strain' => $strain,
        ]);
    }

    /**
     * @Route("/{id}-{slug}/delete", name="strain_delete", requirements={"id": "\d+"})
     * @ParamConverter("strain", options={"repository_method" = "findOneBySlug"})
     * @Method("POST")
     * @Security("strain.isAuthor(user) or strain.getGroup().isAdministrator(user)")
     */
    public function deleteAction(Strain $strain, Request $request)
    {
        $form = $this->createDeleteForm($strain);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($strain);
            $em->flush();
        }

        $this->addFlash('success', 'The strain has been deleted successfully.');

        return $this->redirectToRoute('strain_index');
    }

    /**
     * Creates a form to delete a strain entity.
     *
     * @param Strain $strain The strain entity
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createDeleteForm(Strain $strain)
    {
        return $this->createFormBuilder(null, ['attr' => ['data-confirmation' => true]])
            ->setAction($this->generateUrl('strain_delete', ['id' => $strain->getId(), 'slug' => $strain->getSlug()]))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }

    /**
     * @Route("/autocomplete/group/{group}/name/{name}", name="strain_name_autocomplete", options={"expose"=true}, condition="request.isXmlHttpRequest()")
     */
    public function nameAutocompleteAction($group, $name)
    {
        $repositoryManager = $this->get('fos_elastica.manager.orm');
        $repository = $repositoryManager->getRepository('AppBundle:Strain');
        $elasticQuery = $repository->searchByNameQuery($name, null, $group, $this->getUser());
        $results = $this->get('fos_elastica.finder.app.strain')->find($elasticQuery);

        $data = [];
        foreach ($results as $result) {
            if (!in_array($result->getName(), $data)) {
                $data[] = $result->getName();
            }
        }

        return new JsonResponse($data, 200, [
            'Cache-Control' => 'no-cache',
        ]);
    }
}
