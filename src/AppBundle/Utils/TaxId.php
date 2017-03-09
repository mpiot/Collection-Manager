<?php

namespace AppBundle\Utils;

use AppBundle\Entity\Genus;
use AppBundle\Entity\Species;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DomCrawler\Crawler;

class TaxId
{
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * A constant that contain the api url.
     */
    const NCBI_TAXONOMY_API_LINK = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=taxonomy&id=';

    public function getArray(int $taxid)
    {
        // Initialise the response
        $response = [];

        // Retrieve the page content (xml code)
        if (!$xmlString = @file_get_contents(self::NCBI_TAXONOMY_API_LINK.$taxid)) {
            $response['error'] = 'An error occured';

            return $response;
        }

        // Create a crawler and give the xml code to it
        $crawler = new Crawler($xmlString);

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

        return $response;
    }

    public function getSpecies(int $taxid)
    {
        $array = $this->getArray($taxid);

        if (array_key_exists('error', $array)) {
            return $array['error'];
        }

        // Set the mainSpecies
        $mainSpecies = $this->setSpecies($array['genus'], $array['name'], $taxid, null);

        // Set the synonyms
        if (array_key_exists('synonyms', $array)) {
            foreach ($array['synonyms'] as $synonym) {
                $species = $this->setSpecies($synonym['genus'], $synonym['name'], null, $mainSpecies);
                $mainSpecies->addSynonym($species);
            }
        }

        return $mainSpecies;
    }

    private function setSpecies($genusName, $speciesName, $taxid = null, $mainSpecies = null)
    {
        // Is a genus already exist for this species ?
        $genus = $this->em->getRepository('AppBundle:Genus')->findOneByName($genusName);

        if (null === $genus) {
            $genus = new Genus();
            $genus->setName($genusName);
        }

        $species = new Species();
        $species->setGenus($genus);
        $species->setName($speciesName);
        $species->setTaxId($taxid);
        if ($mainSpecies) {
            $species->setMainSpecies($mainSpecies);
        }

        return $species;
    }
}
