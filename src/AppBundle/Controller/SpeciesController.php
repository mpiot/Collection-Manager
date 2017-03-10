<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Species;
use AppBundle\Form\Type\SpeciesLimitedType;
use AppBundle\Form\Type\SpeciesType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SpeciesController.
 *
 * @Route("/species")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class SpeciesController extends Controller
{
    /**
     * @Route(
     *     "/",
     *     options={"expose"=true},
     *     name="species_index"
     * )
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction($request);

        return $this->render('species/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
        ]);
    }

    /**
     * @Route(
     *     "/list",
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()",
     *     name="species_index_ajax"
     * )
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function listAction(Request $request)
    {
        $query = ('' !== $request->get('q') && null !== $request->get('q')) ? $request->get('q') : null;
        $page = (0 < (int) $request->get('p')) ? $request->get('p') : 1;

        $repositoryManager = $this->get('fos_elastica.manager');
        $repository = $repositoryManager->getRepository('AppBundle:Species');
        $elasticQuery = $repository->searchByScientificNameQuery($query, $page);
        $speciesList = $this->get('fos_elastica.finder.app.species')->find($elasticQuery);
        $nbResults = $this->get('fos_elastica.index.app.species')->count($elasticQuery);

        $nbPages = ceil($nbResults / Species::NUM_ITEMS);

        return $this->render('species/_list.html.twig', [
            'speciesList' => $speciesList,
            'query' => $query,
            'page' => $page,
            'nbPages' => $nbPages,
        ]);
    }

    /**
     * @Route("/add", name="species_add")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function addAction(Request $request)
    {
        if ($this->getUser()->isTeamAdministrator() || $this->getUser()->isProjectAdministrator()) {
            $species = new Species();
            $form = $this->createForm(SpeciesType::class, $species)
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
                $em->persist($species);
                $em->flush();

                $this->addFlash('success', 'The species has been added successfully.');

                $nextAction = $form->get('saveAndAdd')->isClicked()
                    ? 'species_add'
                    : 'species_index';

                return $this->redirectToRoute($nextAction);
            }
        } else {
            $form = $this->createForm(SpeciesLimitedType::class)
                ->add('save', SubmitType::class, [
                    'label' => 'Create',
                    'attr' => [
                        'data-btn-group' => 'btn-group',
                        'data-btn-position' => 'btn-first',
                    ],
                ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $species = $this->get('app.taxid')->getSpecies((int) $data['taxId']);

                if (!$species instanceof Species) {
                    $form->get('taxId')->addError(new FormError($species));

                    return $this->render('species/add.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }

                // Is the species valid ?
                $validator = $this->get('validator');
                $errors = $validator->validate($species);

                if (count($errors) > 0) {
                    $form->addError(new FormError('This taxId refers to already existant species.'));

                    return $this->render('species/add.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }

                $em = $this->getDoctrine()->getManager();
                $em->persist($species);
                $em->flush();

                $this->addFlash('success', 'The species has been added successfully.');

                return $this->redirectToRoute('species_index');
            }
        }

        return $this->render('species/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="species_view")
     * @ParamConverter("species", options={"repository_method" = "findOneWithGenusAndSynonyms"})
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function viewAction(Species $species)
    {
        if (!$species->isMainSpecies()) {
            return $this->redirectToRoute('species_view', ['slug' => $species->getMainSpecies()->getSlug()]);
        }

        return $this->render('species/view.html.twig', [
            'species' => $species,
        ]);
    }

    /**
     * @Route("/{slug}/edit", name="species_edit")
     * @ParamConverter("species", options={"repository_method" = "findOneWithGenus"})
     * @Security("(null === species.getMainSpecies()) and (user.isTeamAdministrator() or user.isProjectAdministrator())")
     */
    public function editAction(Species $species, Request $request)
    {
        $form = $this->createForm(SpeciesType::class, $species);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The species has been edited successfully.');

            return $this->redirectToRoute('species_view', ['slug' => $species->getSlug()]);
        }

        return $this->render('species/edit.html.twig', [
            'species' => $species,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}/delete", name="species_delete")
     * @Method("POST")
     * @ParamConverter("species", options={"repository_method" = "findOneWithGenus"})
     * @Security("(null === species.getMainSpecies()) and (user.isTeamAdministrator() or user.isProjectAdministrator())")
     */
    public function deleteAction(Species $species, Request $request)
    {
        // Check if the species is used in strains, else redirect user
        if (!$species->getStrains()->isEmpty()) {
            $this->addFlash('warning', 'The species cannot be deleted, it\'s used in strain(s).');

            return $this->redirectToRoute('species_view', ['slug' => $species->getSlug()]);
        }

        // If the CSRF token is invalid, redirect user
        if (!$this->isCsrfTokenValid('species_delete', $request->request->get('token'))) {
            $this->addFlash('warning', 'The CSRF token is invalid.');

            return $this->redirectToRoute('species_view', ['slug' => $species->getSlug()]);
        }

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->remove($species);
        $entityManager->flush();

        $this->addFlash('success', 'The species has been deleted successfully.');

        return $this->redirectToRoute('species_index');
    }

    /**
     * Consult the ncbi taxonomy api, and return a json with the interesting data.
     * Used in AddSpecies for the autocomplete method.
     *
     * @param $taxid
     *
     * @return JsonResponse
     *
     * @Route(
     *     "/json/{taxid}",
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()",
     *     name="species_getjson"
     * )
     * @Security("user.isTeamAdministrator() or user.isProjectAdministrator()")
     */
    public function getJsonAction($taxid)
    {
        $data = $this->get('app.taxid')->getArray((int) $taxid);

        return new JsonResponse($data);
    }
}
