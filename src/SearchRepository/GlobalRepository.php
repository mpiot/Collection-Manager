<?php

namespace App\SearchRepository;

use App\Entity\Group;
use App\Entity\User;

class GlobalRepository
{
    public function searchQuery($keyword, User $user, $category = null, $country = null, Group $group = null, User $author = null)
    {
        // Create the search query
        $query = new \Elastica\Query\BoolQuery();

        //-------------------------------//
        // Set queries used in BoolQuery //
        //-------------------------------//

        // If user set a keyword, else, he just want use a filter
        if (null !== $keyword) {
            $keywordQuery = new \Elastica\Query\QueryString();
            $keywordQuery->setFields(['autoName', 'name', 'sequence']);
            $keywordQuery->setDefaultOperator('AND');
            $keywordQuery->setQuery($keyword);
        }

        // Set the country filter
        if (null !== $country && '' !== $country) {
            $countryQuery = new \Elastica\Query\Term();
            // We can't use an analyzer on a term, then we need to lower it here.
            $countryQuery->setTerm('country', mb_strtolower($country));
        }

        // Set the group filter
        if (null !== $group && '' !== $group) {
            $groupQuery = new \Elastica\Query\Term();
            $groupQuery->setTerm('group_id', $group->getId());
        }

        // Set the author filter
        if (null !== $author && '' !== $author) {
            $authorQuery = new \Elastica\Query\Term();
            $authorQuery->setTerm('author_id', $author->getId());
        }

        //----------------------------------------//
        // Set security queries used in BoolQuery //
        //----------------------------------------//

        // Set a group filter
        $groupsSecureQuery = new \Elastica\Query\Terms();
        $groupsSecureQuery->setTerms('group_id', $user->getGroupsId());

        //-------------------------------------------//
        // Assign previous queries to each BoolQuery //
        //-------------------------------------------//

        // For plasmid
        if (null === $category || in_array('plasmid', $category, true)) {
            // Set a specific filter, for this type
            $plasmidTypeQuery = new \Elastica\Query\Type();
            $plasmidTypeQuery->setType('plasmid');

            // Create the BoolQuery, and set a MinNumShouldMatch, to avoid have all results in database
            $plasmidBoolQuery = new \Elastica\Query\BoolQuery();

            // First, define required queries like: type, security
            $plasmidBoolQuery->addFilter($groupsSecureQuery);
            $plasmidBoolQuery->addFilter($plasmidTypeQuery);

            // Then, all conditional queries
            if (null !== $keyword) {
                $plasmidBoolQuery->addShould($keywordQuery);
                $plasmidBoolQuery->setMinimumShouldMatch(1);
            }
            if (null !== $author && '' !== $author) {
                $plasmidBoolQuery->addFilter($authorQuery);
            }
            if (null !== $group && '' !== $group) {
                $plasmidBoolQuery->addFilter($groupQuery);
            }

            // Add the Plasmid BoolQuery to the main BoolQuery
            $query->addShould($plasmidBoolQuery);
        }

        // For primer
        if (null === $category || in_array('primer', $category, true)) {
            // Set a specific filter, for this type
            $primerTypeQuery = new \Elastica\Query\Type();
            $primerTypeQuery->setType('primer');

            // Create the BoolQuery, and set a MinNumShouldMatch, to avoid have all results in database
            $primerBoolQuery = new \Elastica\Query\BoolQuery();

            // First, define required queries like: type, security
            $primerBoolQuery->addFilter($groupsSecureQuery);
            $primerBoolQuery->addFilter($primerTypeQuery);

            // Then, all conditional queries
            if (null !== $keyword) {
                $primerBoolQuery->addShould($keywordQuery);
                $primerBoolQuery->setMinimumShouldMatch(1);
            }
            if (null !== $author && '' !== $author) {
                $primerBoolQuery->addFilter($authorQuery);
            }
            if (null !== $group && '' !== $group) {
                $primerBoolQuery->addFilter($groupQuery);
            }

            // Add the Primer BoolQuery to the main BoolQuery
            $query->addShould($primerBoolQuery);
        }

        // For Gmo Strain
        if (null === $category || in_array('gmo', $category, true)) {
            // Set a specific filter, for this type
            $strainTypeQuery = new \Elastica\Query\Type();
            $strainTypeQuery->setType('strain');

            // Filter on discriminator
            $dicriminatorFilter = new \Elastica\Query\Term();
            $dicriminatorFilter->setTerm('discriminator', 'gmo');

            // Create the BoolQuery, and set a MinNumShouldMatch, to avoid have all results in database
            $gmoStrainBoolQuery = new \Elastica\Query\BoolQuery();

            // First, define required queries like: type, security
            $gmoStrainBoolQuery->addFilter($groupsSecureQuery);
            $gmoStrainBoolQuery->addFilter($strainTypeQuery);
            $gmoStrainBoolQuery->addFilter($dicriminatorFilter);

            // Then, all conditional queries
            if (null !== $keyword) {
                $gmoStrainBoolQuery->addShould($keywordQuery);
                $gmoStrainBoolQuery->setMinimumShouldMatch(1);
            }
            if (null !== $author && '' !== $author) {
                $gmoStrainBoolQuery->addFilter($authorQuery);
            }
            if (null !== $group && '' !== $group) {
                $gmoStrainBoolQuery->addFilter($groupQuery);
            }

            // Add the Gmo BoolQuery to the main BoolQuery
            $query->addShould($gmoStrainBoolQuery);
        }

        // For wild strain
        if (null === $category || in_array('wild', $category, true)) {
            // Set a specific filter, for this type
            $wildTypeQuery = new \Elastica\Query\Type();
            $wildTypeQuery->setType('strain');

            // Filter on discriminator
            $dicriminatorFilter = new \Elastica\Query\Term();
            $dicriminatorFilter->setTerm('discriminator', 'wild');

            // Create the BoolQuery, and set a MinNumShouldMatch, to avoid have all results in database
            $wildStrainBoolQuery = new \Elastica\Query\BoolQuery();

            // First, define required queries like: type, security
            $wildStrainBoolQuery->addFilter($groupsSecureQuery);
            $wildStrainBoolQuery->addFilter($wildTypeQuery);
            $wildStrainBoolQuery->addFilter($dicriminatorFilter);

            // Then, all conditional queries
            if (null !== $keyword) {
                $wildStrainBoolQuery->addShould($keywordQuery);
                $wildStrainBoolQuery->setMinimumShouldMatch(1);
            }
            if (null !== $author && '' !== $author) {
                $wildStrainBoolQuery->addFilter($authorQuery);
            }
            if (null !== $country && '' !== $country) {
                $wildStrainBoolQuery->addFilter($countryQuery);
            }
            if (null !== $group && '' !== $group) {
                $wildStrainBoolQuery->addFilter($groupQuery);
            }

            // Add the Gmo BoolQuery to the main BoolQuery
            $query->addShould($wildStrainBoolQuery);
        }

        // For product
        if (null === $category || in_array('product', $category, true)) {
            // Set a specific filter, for this type
            $productTypeQuery = new \Elastica\Query\Type();
            $productTypeQuery->setType('product');

            // Create the BoolQuery, and set a MinNumShouldMatch, to avoid have all results in database
            $productBoolQuery = new \Elastica\Query\BoolQuery();

            // First, define required queries like: type, security
            $productBoolQuery->addFilter($groupsSecureQuery);
            $productBoolQuery->addFilter($productTypeQuery);

            // Then, all conditional queries
            if (null !== $keyword) {
                $productBoolQuery->addShould($keywordQuery);
                $productBoolQuery->setMinimumShouldMatch(1);
            }
            if (null !== $group && '' !== $group) {
                $productBoolQuery->addFilter($groupQuery);
            }

            // Add the Primer BoolQuery to the main BoolQuery
            $query->addShould($productBoolQuery);
        }

        // For equipment
        if (null === $category || in_array('equipment', $category, true)) {
            // Set a specific filter, for this type
            $equipmentTypeQuery = new \Elastica\Query\Type();
            $equipmentTypeQuery->setType('equipment');

            // Create the BoolQuery, and set a MinNumShouldMatch, to avoid have all results in database
            $equipmentBoolQuery = new \Elastica\Query\BoolQuery();

            // First, define required queries like: type, security
            $equipmentBoolQuery->addFilter($groupsSecureQuery);
            $equipmentBoolQuery->addFilter($equipmentTypeQuery);

            // Then, all conditional queries
            if (null !== $keyword) {
                $equipmentBoolQuery->addShould($keywordQuery);
                $equipmentBoolQuery->setMinimumShouldMatch(1);
            }
            if (null !== $group && '' !== $group) {
                $equipmentBoolQuery->addFilter($groupQuery);
            }

            // Add the Primer BoolQuery to the main BoolQuery
            $query->addShould($equipmentBoolQuery);
        }

        return $query;
    }
}
