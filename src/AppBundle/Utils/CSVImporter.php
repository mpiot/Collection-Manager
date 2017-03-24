<?php

namespace AppBundle\Utils;

use AppBundle\Entity\Box;
use AppBundle\Entity\Strain;
use AppBundle\Entity\Tube;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CSVImporter
{
    private $em;
    private $validator;

    public function __construct(EntityManager $entityManager, ValidatorInterface $validator)
    {
        $this->em = $entityManager;
        $this->validator = $validator;
    }

    public function importBox(Box $box, Form $form)
    {
        $project = $box->getProject();
        $team = $project->getTeam();
        $file = $form->get('csvFile')->getData()->getRealPath();

        $row = 0;
        $data = [];
        if (false !== ($handle = fopen($file, "r"))) {
            while (false !== ($line = fgetcsv($handle, 1000, ","))) {
                ++$row;
                if (1 === $row) {
                    continue;
                }

                $data[] = $line;
            }
            fclose($handle);
        }

        // If the file contains more strain than empty cells in the box
        if (count($data) > $box->getFreeSpace()) {
            return $form->addError(new FormError('There is not enough cells in the box. ('.$box->getFreeSpace().' cells in the box, and the file contain '.count($data).' strains.'));
        }

        $validObjects = [
            'species' => [],
            'type' => [],
            'category' => [],
        ];

        $emptyCells = array_values($box->getEmptyCells());
        $row = 1;

        foreach ($data as $line) {
            ++$row;

            $tube = new Tube();
            $tube->setProject($project);
            $tube->setBox($box);
            $tube->setCell($emptyCells[0]);
            unset($emptyCells[0]);
            $emptyCells = array_values($emptyCells);

            $strain = new Strain();
            $strain->addTube($tube);
            $strain->setDiscriminator($line[0]);
            $strain->setName($line[4]);
            $strain->setComment($line[5]);
            $sequenced = 'no' === $line[6] || empty($line[6]) ? false : true;
            $strain->setSequenced($sequenced);

            // Check species
            //Before do a Doctrine query, check if we already have validate it
            if (!array_key_exists($line[1].' '.$line[2], $validObjects['species'])) {
                $genus = $this->em->getRepository('AppBundle:Genus')->findOneByName($line[1]);
                if (null === $genus) {
                    return $form->addError(new FormError('The genus '.$line[1].' doesn\'t exists.'));
                }

                $species = $this->em->getRepository('AppBundle:Species')->findOneBy(['genus' => $genus, 'name' => $line[2]]);
                if (null === $species) {
                    return $form->addError(new FormError('The species '.$line[1].' '.$line[2].' doesn\'t exists.'));
                }

                // Add in a array all valid data to prevent some db requests
                $validObjects['species'][$line[1].' '.$line[2]] = $species;
            }
            $strain->setSpecies($validObjects['species'][$line[1].' '.$line[2]]);

            // Check type
            //Before do a Doctrine query, check if we already have validate it
            if (!array_key_exists($line[3], $validObjects['type'])) {
                $type = $this->em->getRepository('AppBundle:Type')->findOneBy(['team' => $team, 'name' => $line[3]]);
                if (null === $type) {
                    return $form->addError(new FormError('The type "'.$line[3].'"" doesn\'t exists for the team: "'.$team->getName().'"".'));
                }

                // Add in a array all valid data to prevent some db requests
                $validObjects['type'][$line[3]] = $type;
            }
            $strain->setType($validObjects['type'][$line[3]]);

            // Special fields, only for GMO or only for Wild
            if ('gmo' === $strain->getDiscriminator()) {
                $strain->setDescription($line[7]);
                $strain->setGenotype($line[8]);

            } elseif ('wild' === $strain->getDiscriminator()) {
                // Check category
                //Before do a Doctrine query, check if we already have validate it
                if (!array_key_exists($line[9], $validObjects['category'])) {
                    $category = $this->em->getRepository('AppBundle:BiologicalOriginCategory')->findOneBy(['team' => $team, 'name' => $line[9]]);
                    if (null === $category) {
                        return $form->addError(new FormError('The category "'.$line[9].'"" doesn\'t exists for the team: "'.$team->getName().'"".'));
                    }

                    // Add in a array all valid data to prevent some db requests
                    $validObjects['category'][$line[9]] = $category;
                }
                $strain->setBiologicalOriginCategory($validObjects['category'][$line[9]]);

                $strain->setBiologicalOrigin($line[10]);
                $strain->setSource($line[11]);
                $strain->setLatitude($line[12]);
                $strain->setLongitude($line[13]);
                $strain->setAddress($line[14]);
                $strain->setCountry($line[15]);
            }

            // Is the strain deleted ?
            $deleted = 'no' === $line[16] || empty($line[16]) ? false : true;
            $strain->setDeleted($deleted);
            $tube->setDeleted($deleted);

            // Control with validator, object is OK
            $errors = $this->validator->validate($strain);
            if (count($errors) > 0) {
                $errorString = 'Line '.$row.', Column '.$errors[0]->getPropertyPath().': '.$errors[0]->getMessage();

                return $form->addError(new FormError($errorString));
            }

            // Persist the strain
            $this->em->persist($strain);
        }

        // Flush all
        $this->em->flush();

        return $form;
    }
}
