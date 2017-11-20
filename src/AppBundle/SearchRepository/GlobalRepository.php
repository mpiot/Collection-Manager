<?php

namespace AppBundle\SearchRepository;

use AppBundle\Entity\Group;
use AppBundle\Entity\User;

class GlobalRepository
{
    public function searchQuery($keyword = null, User $user, $category = null, $country = null, Group $group = null, User $author = null)
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
            $countryQuery->setTerm('country', strtolower($country));
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
        if (null === $category || in_array('plasmid', $category)) {
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

            // Add the Plasmid BoolQuery to the main BoolQuery
            // Set a boost on 2, because there is 2 fields in "should"
            $query->addShould($plasmidBoolQuery->setBoost(2));
        }

        // For primer
        if (null === $category || in_array('primer', $category)) {
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

            // Add the Primer BoolQuery to the main BoolQuery
            // Set a boost on 3, because there is 3 fields in "should"
            $query->addShould($primerBoolQuery->setBoost(3));
        }

        // For Gmo Strain
        if (null === $category || in_array('gmo', $category)) {
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
            // Set a boost on 2, because there is 2 fields in "should"
            $query->addShould($gmoStrainBoolQuery->setBoost(2));
        }

        // For wild strain
        if (null === $category || in_array('wild', $category)) {
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
            // Set a boost on 2, because there is 2 fields in "should"
            $query->addShould($wildStrainBoolQuery->setBoost(2));
        }

        return $query;
    }
}
