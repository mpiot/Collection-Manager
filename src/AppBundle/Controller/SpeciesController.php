<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Species;
use AppBundle\Form\Type\SpeciesType;
use AppBundle\Utils\TaxId;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
        $page = (0 < (int) $request->get('p')) ? $request->get('p') : 1 ;

        $repositoryManager = $this->container->get('fos_elastica.manager.orm');
        $repository = $repositoryManager->getRepository('AppBundle:Species');
        $speciesList = $repository->findByScientificName($query, $page, self::HIT_PER_PAGE);

        $nbPages = ceil($speciesList->getNbResults() / self::HIT_PER_PAGE);

        return $this->render('species/list.html.twig', [
            'speciesList' => $speciesList,
            'query'       => $query,
            'page'        => $page,
            'nbPages'     => $nbPages,
        ]);
    }

    /**
     * @Route("/add", name="species_add")
     * @Security("user.isTeamAdministrator() or user.isProjectAdministrator() or is_granted('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        $species = new Species();
        $form = $this->createForm(SpeciesType::class, $species);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($species);
            $em->flush();

            $this->addFlash('success', 'The species has been added successfully.');

            return $this->redirectToRoute('species_index');
        }

        return $this->render('species/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="species_edit")
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
     * @Route("/delete/{id}", name="species_delete")
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

        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($species);
            $em->flush();

            $this->addFlash('success', 'The species has been deleted successfully.');

            return $this->redirectToRoute('species_index');
        }

        return $this->render('species/delete.html.twig', [
            'species' => $species,
            'form' => $form->createView(),
        ]);
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
