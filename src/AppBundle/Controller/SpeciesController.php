<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Species;
use AppBundle\Form\Type\SpeciesType;
use AppBundle\Utils\TaxId;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
    const HIT_PER_PAGE = 10;

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

        $repositoryManager = $this->get('fos_elastica.manager.orm');
        $repository = $repositoryManager->getRepository('AppBundle:Species');
        $elasticQuery = $repository->searchByScientificNameQuery($query, $page, self::HIT_PER_PAGE);
        $nbResults = $this->get('fos_elastica.index.app.species')->count($elasticQuery);
        $finder = $this->get('fos_elastica.finder.app.species');
        $speciesList = $finder->find($elasticQuery);

        $nbPages = ceil($nbResults / self::HIT_PER_PAGE);

        return $this->render('species/_list.html.twig', [
            'speciesList' => $speciesList,
            'query' => $query,
            'page' => $page,
            'nbPages' => $nbPages,
        ]);
    }

    /**
     * @Route("/{id}", name="species_view")
     * @ParamConverter("species", class="AppBundle:Species", options={
     *     "repository_method" = "findOneWithGenusAndSynonyms"
     * })
     */
    public function viewAction(Species $species)
    {
        if (!$species->isMainSpecies()) {
            $this->addFlash('warning', 'This is not a main species.');

            return $this->redirectToRoute('species_index');
        }

        return $this->render('species/view.html.twig', [
            'species' => $species,

        ]);
    }

    /**
     * @Route("/add", name="species_add")
     * @Security("user.isTeamAdministrator() or user.isProjectAdministrator() or is_granted('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
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

        return $this->render('species/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="species_edit")
     * @ParamConverter("species", class="AppBundle:Species", options={
     *     "repository_method" = "findOneWithGenus"
     * })
     * @Security("(null === species.getMainSpecies()) and (user.isTeamAdministrator() or user.isProjectAdministrator() or is_granted('ROLE_ADMIN'))")
     */
    public function editAction(Species $species, Request $request)
    {
        $form = $this->createForm(SpeciesType::class, $species);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The species has been edited successfully.');

            return $this->redirectToRoute('species_index');
        }

        return $this->render('species/edit.html.twig', [
            'species' => $species,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="species_delete")
     * @Method("POST")
     * @ParamConverter("species", class="AppBundle:Species", options={
     *     "repository_method" = "findOneWithGenus"
     * })
     * @Security("(null === species.getMainSpecies()) and (user.isTeamAdministrator() or user.isProjectAdministrator() or is_granted('ROLE_ADMIN'))")
     */
    public function deleteAction(Species $species, Request $request)
    {
        // Check if the species is used in strains, else redirect user
        if (!$species->getGmoStrains()->isEmpty() || !$species->getWildStrains()->isEmpty()) {
            $this->addFlash('warning', 'The species cannot be deleted, it\'s used in strain(s).');

            return $this->redirectToRoute('species_index');
        }

        // If the CSRF token is invalid, redirect user
        if (!$this->isCsrfTokenValid('species_delete', $request->request->get('token'))) {
            $this->addFlash('warning', 'The CSRF token is invalid.');

            return $this->redirectToRoute('species_index');
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
     * @Route("/json/{taxid}", name="species_getjson", condition="request.isXmlHttpRequest()")
     */
    public function getJsonAction($taxid)
    {
        $taxIdChecker = new TaxId();
        $data = $taxIdChecker->getArray((int) $taxid);

        return new JsonResponse($data);
    }
}
