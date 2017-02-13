<?php

namespace AppBundle\SearchRepository;

use AppBundle\Entity\Box;
use AppBundle\Entity\User;
use FOS\ElasticaBundle\Repository;

class BoxRepository extends Repository
{
    public function searchByNameQuery($q, $p, User $user)
    {
        $projectsId = $user->getProjectsId();

        if ($user->isTeamAdministrator()) {
            $administeredTeams = $user->getAdministeredTeams();
            $teamsProjects = [];

            foreach($administeredTeams as $team) {
                $teamsProjects = array_merge($teamsProjects, $team->getProjects()->toArray());
            }

            foreach ($teamsProjects as $project) {
                if (!in_array($id = $project->getId(), $projectsId)) {
                    $projectsId[] = $id;
                }
            }
        }

        $query = new \Elastica\Query();

        if (null !== $q) {
            $queryString = new \Elastica\Query\QueryString();
            $queryString->setFields(['name', 'autoName', 'project']);
            $queryString->setDefaultOperator('AND');
            $queryString->setQuery($q);

            $query->setQuery($queryString);
        } else {
            $matchAllQuery = new \Elastica\Query\MatchAll();

            $query->setQuery($matchAllQuery);
            $query->setSort(['name_raw' => 'asc']);
        }

        $projectFilter = new \Elastica\Query\Terms();
        $projectFilter->setTerms('project_id', $projectsId);

        $query->setPostFilter($projectFilter);

        $query
            ->setFrom(($p - 1) * Box::NUM_ITEMS)
            ->setSize(Box::NUM_ITEMS);

        // build $query with Elastica objects
        return $query;
    }
}
