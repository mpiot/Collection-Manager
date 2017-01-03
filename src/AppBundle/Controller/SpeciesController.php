<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Species;
use AppBundle\Form\Type\SpeciesType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\DomCrawler\Crawler;
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
     * A constant that contain the api url.
     */
    const NCBI_TAXONOMY_API_LINK = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=taxonomy&id=';

    /**
     * @Route("/", name="species_index")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $species = $em->getRepository('AppBundle:Species')->findAllWithGenus();

        return $this->render('species/index.html.twig', [
            'speciesList' => $species,
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
     * @Route("/json/{taxid}", name="species_getjson")
     */
    public function getJsonAction($taxid)
    {
        // Retrieve the page content (xml code)
        $xmlString = file_get_contents(self::NCBI_TAXONOMY_API_LINK.$taxid);

        // Create a crawler and give the xml code to it
        $crawler = new Crawler($xmlString);

        // Initialise the response
        $response = [];

        // Count the number of taxon tag, if different of 0 there are contents, else the document is empty, it's because the Taxon Id doesn't exists
        if (0 !== $crawler->filterXPath('//TaxaSet/Taxon')->count()) {
            // If the tag Rank contain 'species', the Id match on a species, else, it's not correct.
            if ('species' === $crawler->filterXPath('//TaxaSet/Taxon/Rank')->text()) {
                // Use the crawler to crawl the document and fill the response
                $response['scientificName'] = $crawler->filterXPath('//TaxaSet/Taxon/ScientificName')->text();

                // Explode the scientific name to retrieve: genus and species
                $scientificNameExploded = explode(' ', $response['scientificName']);
                $response['genus'] = $scientificNameExploded[0];
                $response['name'] = $scientificNameExploded[1];

                // He re count the number of synonym tag, if the count is different to 0, there are synonymes
                if (0 !== $crawler->filterXPath('//TaxaSet/Taxon/OtherNames/Synonym')->count()) {
                    // Use a closure on the tag Synonym to extract all synonymes and fill an array
                    $synonyms = $crawler->filterXPath('//TaxaSet/Taxon/OtherNames/Synonym')->each(function (Crawler $node) {
                        return $node->text();
                    });

                    $i = 0;

                    foreach ($synonyms as $synonym) {
                        $scientificNameExploded = explode(' ', $synonym, 2);
                        $genus = $scientificNameExploded[0];
                        $species = $scientificNameExploded[1];

                        $response['synonyms'][$i]['genus'] = $genus;
                        $response['synonyms'][$i]['name'] = $species;

                        ++$i;
                    }

                }
            } else {
                $response['error'] = 'This ID does not match on a species';
            }
        } else {
            $response['error'] = 'This ID does not exists';
        }

        return new JsonResponse($response);
    }
}
