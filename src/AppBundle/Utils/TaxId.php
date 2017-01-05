<?php

namespace AppBundle\Utils;

use Symfony\Component\DomCrawler\Crawler;

class TaxId
{
    /**
     * A constant that contain the api url.
     */
    const NCBI_TAXONOMY_API_LINK = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=taxonomy&id=';

    public function getArray(int $taxid)
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

        return $response;
    }
}
