<?php
// src/AppBundle/DataFixtures/ORM/LoadFixtures.php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Box;
use AppBundle\Entity\Genus;
use AppBundle\Entity\GmoStrain;
use AppBundle\Entity\Project;
use AppBundle\Entity\Species;
use AppBundle\Entity\Tube;
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
        //-----------------//
        // Genus & Species //
        //-----------------//
        $speciesData = [
            [
                'genus' => 'Candida',
                'species' =>
                    [
                        'albicans',
                        'dubliniensis',
                        'glabrata',
                    ]
            ],
            [
                'genus' => 'Escherichia',
                'species' =>
                    [
                        'coli',
                    ]
            ],
            [
                'genus' => 'Yarrowia',
                'species' =>
                    [
                        'bubula',
                        'deformans',
                        'lipolytica',
                    ]
            ],
        ];

        foreach ($speciesData as $data) {
            $genus = new Genus();
            $genus->setGenus($data['genus']);

            $manager->persist($genus);
            $this->setReference('genus-'.$data['genus'], $genus);

            foreach($data['species'] as $speciesName) {
                $species = new Species();
                $species->setGenus($this->getReference('genus-'.$data['genus']));
                $species->setSpecies($speciesName);

                $manager->persist($species);
                $this->setReference('species-'.$speciesName, $species);
            }
        }

        //-------//
        // Types //
        //-------//
        $typesData = [
            ['name' => 'Yeast', 'letter' => 'Y'],
            ['name' => 'E. coli', 'letter' => 'E'],
            ['name' => 'Plasmid', 'letter' => 'P'],
        ];

        foreach($typesData as $typeData) {
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
                'name' => 'DivYN',
                'prefix' => 'DIVYN',
                'description' => 'The DivYN project',
            ],
            [
                'name' => 'EJC-NMD',
                'prefix' => 'EJC-NMD',
                'description' => 'The EJC - NMD project.',
            ],
        ];

        foreach($projectsData as $projectData) {
            $project = new Project();
            $project->setName($projectData['name']);
            $project->setPrefix($projectData['prefix']);
            $project->setDescription($projectData['description']);

            $manager->persist($project);
            $this->setReference('project-'.$projectData['prefix'], $project);
        }

        //-------//
        // Boxes //
        //-------//
        $boxesData = [
            [
                'project' => $this->getReference('project-DIVYN'),
                'name' => 'DivYN - Box 1',
                'letter' => 'A',
                'description' => 'The 1st box in the DivYN project.',
                'type' => $this->getReference('type-Yeast'),
                'freezer' => 'Emile',
                'location' => '1st Shelve - 1st rack on the left - 2nd Column in the rack - 3rd box in the column',
                'colNumber' => '10',
                'rowNumber' => '10',
            ],
            [
                'project' => $this->getReference('project-DIVYN'),
                'name' => 'DivYN - Box 2',
                'letter' => 'B',
                'description' => 'The 2nd box in the DivYN project.',
                'type' => $this->getReference('type-Yeast'),
                'freezer' => 'Emile',
                'location' => '1st Shelve - 1st rack on the left - 2nd Column in the rack - 4th box in the column',
                'colNumber' => '9',
                'rowNumber' => '9',
            ],
            [
                'project' => $this->getReference('project-EJC-NMD'),
                'name' => 'EJC-NMD - Box 1',
                'letter' => 'A',
                'description' => 'The 1st box in tje EJC-NMD project.',
                'type' => $this->getReference('type-E. coli'),
                'freezer' => 'Emile',
                'location' => '1st Shelve - 1st rack on the left - 3rd Column in the rack - 1st box in the column',
                'colNumber' => '8',
                'rowNumber' => '8',
            ],
            [
                'project' => $this->getReference('project-EJC-NMD'),
                'name' => 'EJC-NMD - Box 2',
                'letter' => 'B',
                'description' => 'The 2nd box in tje EJC-NMD project.',
                'type' => $this->getReference('type-Yeast'),
                'freezer' => 'Emile',
                'location' => '1st Shelve - 1st rack on the left - 3rd Column in the rack - 2nd box in the column',
                'colNumber' => '10',
                'rowNumber' => '10',
            ],
        ];

        foreach($boxesData as $boxData) {
            $box = new Box();
            $box->setProject($boxData['project']);
            $box->setName($boxData['name']);
            $box->setDescription($boxData['description']);
            $box->setType($boxData['type']);
            $box->setFreezer($boxData['freezer']);
            $box->setLocation($boxData['location']);
            $box->setColNumber($boxData['colNumber']);
            $box->setRowNumber($boxData['rowNumber']);

            $manager->persist($box);
            $this->setReference('box-'.$boxData['name'], $box);
        }
/*
        //---------//
        // Strains //
        //---------//

        //GmoStrains
        $gmoStrainsData = [
            [
                'species' => $this->getReference('species-coli'),
                'type' => $this->getReference('type-E. coli'),
                'usualName' => 'Mach1T1',
                'systematicName' => '',
                'comment' => 'Strain to do competents cells.',
                'sequenced' => true,
                'deleted' => false,
                'description' => 'Idem than comment',
                'genotype' => '-',
                'tubes' => [
                    [
                        'box' => $this->getReference('box-EJC-NMD - Box 1'),
                        'cell' => 0,
                    ],
                    [
                        'box' => $this->getReference('box-EJC-NMD - Box 1'),
                        'cell' => 1,
                    ],
                ]
            ],
            [
                'species' => $this->getReference('species-lipolytica'),
                'type' => $this->getReference('type-Yeast'),
                'usualName' => 'E150',
                'systematicName' => '',
                'comment' => '-',
                'sequenced' => true,
                'deleted' => false,
                'description' => '-',
                'genotype' => '-',
                'tubes' => [
                    [
                        'box' => $this->getReference('box-EJC-NMD - Box 2'),
                        'cell' => 0,
                    ],
                    [
                        'box' => $this->getReference('box-EJC-NMD - Box 2'),
                        'cell' => 1,
                    ],
                    [
                        'box' => $this->getReference('box-DivYN - Box 1'),
                        'cell' => 0,
                    ],
                ]
            ],
        ];

        foreach ($gmoStrainsData as $strainData) {
            $strain = new GmoStrain();
            $strain->setSpecies($strainData['species']);
            $strain->setType($strainData['type']);
            $strain->setUsualName($strainData['usualName']);
            $strain->setSystematicName($strainData['systematicName']);
            $strain->setComment($strainData['comment']);
            $strain->setSequenced($strainData['sequenced']);
            $strain->setDeleted($strainData['deleted']);
            $strain->setDescription($strainData['description']);
            $strain->setGenotype($strainData['genotype']);

            foreach($strainData['tubes'] as $tubeData) {
                $tube = new Tube();
                $tube->setBox($tubeData['box']);
                $tube->setCell($tubeData['cell']);

                $strain->addTube($tube);
            }

            $manager->persist($strain);
        }
*/
        // At the end: write all in database
        $manager->flush();
    }
}
