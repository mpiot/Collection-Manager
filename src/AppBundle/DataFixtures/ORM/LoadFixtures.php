<?php
// src/AppBundle/DataFixtures/ORM/LoadFixtures.php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Box;
use AppBundle\Entity\Project;
use AppBundle\Entity\Team;
use AppBundle\Entity\Type;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Load Fixtures.
 * Just fill the database with some data for development.
 */
class LoadFixtures extends AbstractFixture implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        //---------//
        //  Users  //
        //---------//
        $userManager = $this->container->get('fos_user.user_manager');

        // Create a user: admin
        $admin = $userManager->createUser();
        $admin->setEmail('admin');
        $admin->setUsername('admin');
        $admin->setPlainPassword('mdp');
        $admin->setEnabled(true);
        $admin->addRole('ROLE_SUPER_ADMIN');

        // Create a user: user
        $user = $userManager->createUser();
        $user->setEmail('user');
        $user->setUsername('user');
        $user->setPlainPassword('mdp');
        $user->setEnabled(true);

        // Create a user: Team1Admin
        $team1admin = $userManager->createUser();
        $team1admin->setEmail('team1admin');
        $team1admin->setUsername('team1admin');
        $team1admin->setPlainPassword('mdp');
        $team1admin->setEnabled(true);

        // Create a user: Team1Project
        $team1project = $userManager->createUser();
        $team1project->setEmail('team1project');
        $team1project->setUsername('team1project');
        $team1project->setPlainPassword('mdp');
        $team1project->setEnabled(true);

        // Create a user: Team1User
        $team1user = $userManager->createUser();
        $team1user->setEmail('team1user');
        $team1user->setUsername('team1user');
        $team1user->setPlainPassword('mdp');
        $team1user->setEnabled(true);

        // Create a user: Team2Admin
        $team2admin = $userManager->createUser();
        $team2admin->setEmail('team2admin');
        $team2admin->setUsername('team2admin');
        $team2admin->setPlainPassword('mdp');
        $team2admin->setEnabled(true);

        // Create a user: Team2Project
        $team2project = $userManager->createUser();
        $team2project->setEmail('team2project');
        $team2project->setUsername('team2project');
        $team2project->setPlainPassword('mdp');
        $team2project->setEnabled(true);

        // Create a user: Team2User
        $team2user = $userManager->createUser();
        $team2user->setEmail('team2user');
        $team2user->setUsername('team2user');
        $team2user->setPlainPassword('mdp');
        $team2user->setEnabled(true);

        // Persist and setReference for users
        $manager->persist($admin);
        $manager->persist($admin);
        $manager->persist($user);
        $manager->persist($team1admin);
        $manager->persist($team1project);
        $manager->persist($team1user);
        $manager->persist($team2admin);
        $manager->persist($team2project);
        $manager->persist($team2user);

        $this->setReference('user-admin', $admin);
        $this->setReference('user-user', $user);
        $this->setReference('user-team1admin', $team1admin);
        $this->setReference('user-team1project', $team1project);
        $this->setReference('user-team1user', $team1user);
        $this->setReference('user-team2admin', $team2admin);
        $this->setReference('user-team2project', $team2project);
        $this->setReference('user-team2user', $team2user);

        //-------//
        // Teams //
        //-------//
        $teamsData = [
            [
                'name' => 'Team 1',
                'administrators' => [$this->getReference('user-team1admin')],
                'members' => [$this->getReference('user-team1project'), $this->getReference('user-team1user')],
            ],
            [
                'name' => 'Team 2',
                'administrators' => [$this->getReference('user-team2admin')],
                'members' => [$this->getReference('user-team2project'), $this->getReference('user-team2user')],
            ],
        ];

        foreach ($teamsData as $teamData) {
            $team = new Team();
            $team->setName($teamData['name']);

            // Foreach on Administrators
            foreach ($teamData['administrators'] as $administrator) {
                $team->addAdministrator($administrator);
            }

            // Foreach on Members
            foreach ($teamData['members'] as $member) {
                $team->addMember($member);
            }

            $manager->persist($team);
            $this->setReference('team-'.$teamData['name'], $team);
        }

        //-------//
        // Types //
        //-------//
        $typesData = [
            ['name' => 'Yeast', 'letter' => 'Y'],
            ['name' => 'Bacteria', 'letter' => 'B'],
            ['name' => 'Plasmid', 'letter' => 'P'],
        ];

        foreach ($typesData as $typeData) {
            $type = new Type();
            $type->setName($typeData['name']);
            $type->setLetter($typeData['letter']);

            $manager->persist($type);
            $this->setReference('type-'.$typeData['name'], $type);
        }

        //---------//
        // Project //
        //---------//
        $projectsData = [
            [
                'name' => 'Team1 Project',
                'prefix' => 'T1P',
                'description' => 'The first Team 1 project',
                'team' => $this->getReference('team-Team 1'),
                'administrators' => [$this->getReference('user-team1project')],
                'members' => [$this->getReference('user-team1user')],
            ],
            [
                'name' => 'Team2 Project',
                'prefix' => 'T2P',
                'description' => 'The first Team 2 project',
                'team' => $this->getReference('team-Team 2'),
                'administrators' => [$this->getReference('user-team2project')],
                'members' => [$this->getReference('user-team2user')],
            ],
        ];

        foreach ($projectsData as $projectData) {
            $project = new Project();
            $project->setName($projectData['name']);
            $project->setPrefix($projectData['prefix']);
            $project->setDescription($projectData['description']);
            $project->setTeam($projectData['team']);

            // Foreach on Administrators
            foreach ($projectData['administrators'] as $administrator) {
                $project->addAdministrator($administrator);
            }

            // Foreach on Members
            foreach ($projectData['members'] as $member) {
                $project->addMember($member);
            }

            $manager->persist($project);
            $this->setReference('project-'.$projectData['prefix'], $project);
        }

        //-------//
        // Boxes //
        //-------//
        $boxesData = [
            [
                'project' => $this->getReference('project-T1P'),
                'name' => 'T1P - Box 1',
                'description' => 'The 1st box in the T1P project.',
                'freezer' => 'Emile',
                'location' => '1st Shelve - 1st rack on the left - 2nd Column in the rack - 1st box in the column',
                'colNumber' => '10',
                'rowNumber' => '10',
            ],
            [
                'project' => $this->getReference('project-T1P'),
                'name' => 'T1P - Box 2',
                'description' => 'The 2nd box in the T1P project.',
                'freezer' => 'Emile',
                'location' => '1st Shelve - 1st rack on the left - 2nd Column in the rack - 2nd box in the column',
                'colNumber' => '9',
                'rowNumber' => '9',
            ],
            [
                'project' => $this->getReference('project-T2P'),
                'name' => 'T2P - Box 1',
                'description' => 'The 1st box in the T2P project.',
                'freezer' => 'Emile',
                'location' => '1st Shelve - 1st rack on the left - 3rd Column in the rack - 1st box in the column',
                'colNumber' => '8',
                'rowNumber' => '8',
            ],
            [
                'project' => $this->getReference('project-T2P'),
                'name' => 'T2P - Box 2',
                'description' => 'The 2nd box in the T2P project.',
                'freezer' => 'Emile',
                'location' => '1st Shelve - 1st rack on the left - 3rd Column in the rack - 2nd box in the column',
                'colNumber' => '10',
                'rowNumber' => '10',
            ],
        ];

        foreach ($boxesData as $boxData) {
            $box = new Box();
            $box->setProject($boxData['project']);
            $box->setName($boxData['name']);
            $box->setDescription($boxData['description']);
            $box->setFreezer($boxData['freezer']);
            $box->setLocation($boxData['location']);
            $box->setColNumber($boxData['colNumber']);
            $box->setRowNumber($boxData['rowNumber']);

            $manager->persist($box);
            $this->setReference('box-'.$boxData['name'], $box);
        }

        // At the end: write all in database
        $manager->flush();
    }
}
