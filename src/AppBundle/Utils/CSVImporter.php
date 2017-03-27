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
        $errors = [];
        $keys = ['disc', 'genus', 'species', 'type', 'name', 'comment', 'sequenced', 'description', 'genotype', 'biologicalOriginCategory', 'biologicalOrigin', 'source', 'lat', 'long', 'address', 'country', 'deleted'];
        $data = [];

        $row = 0;
        if (false !== ($handle = fopen($file, 'r'))) {
            while (false !== ($line = fgetcsv($handle, 1000, ','))) {
                ++$row;
                if (1 === $row) {
                    continue;
                }

                $data[] = array_combine($keys, $line);
            }
            fclose($handle);
        }

        // If the file contains more strain than empty cells in the box
        if ($row > $box->getFreeSpace()) {
            return $form->addError(new FormError('There is not enough cells in the box. ('.$box->getFreeSpace().' cells in the box, and the file contain '.$row.' lines.'));
        }

        // Change all empty values by null
        array_walk_recursive($data, function (&$value) {
            $value = trim($value);
            if ('' == $value) {
                $value = null;
            }
        });

        // Prepare an array to save SQL requests
        $validObjects = [
            'species' => [],
            'type' => [],
            'category' => [],
        ];

        // Get the empty cells array
        $emptyCells = array_values($box->getEmptyCells());

        foreach ($data as $key => $value) {
            // Init a tube
            $tube = new Tube();
            $tube->setProject($project);
            $tube->setBox($box);
            $tube->setCell($emptyCells[0]);
            // After set a cell, we remove the cell from the array, and re-organize keys for the next
            unset($emptyCells[0]);
            $emptyCells = array_values($emptyCells);

            // Create a new strain, and set attributes
            $strain = new Strain();
            $strain->addTube($tube);
            $strain->setDiscriminator($value['disc']);
            $strain->setName($value['name']);
            $strain->setComment($value['comment']);
            $strain->setDescription($value['description']);
            $strain->setGenotype($value['genotype']);
            $strain->setBiologicalOrigin($value['biologicalOrigin']);
            $strain->setSource($value['source']);
            $strain->setLatitude($value['lat']);
            $strain->setLongitude($value['long']);
            $strain->setAddress($value['address']);
            $strain->setCountry($value['country']);

            // Is the strain sequenced ? (default: false)
            $sequenced = 'yes' === $value['sequenced'] ? true : false;
            $strain->setSequenced($sequenced);

            // Is the strain deleted ? (default: false)
            $deleted = 'yes' === $value['deleted'] ? true : false;
            $strain->setDeleted($deleted);
            $tube->setDeleted($deleted);

            // Check species
            //Before do a Doctrine query, check if we already have validate it
            if (!array_key_exists($value['genus'].' '.$value['species'], $validObjects['species'])) {
                $genus = $this->em->getRepository('AppBundle:Genus')->findOneByName($value['genus']);
                if (null === $genus) {
                    $errorString = 'Line '.($key + 2).', Column "genus": The genus "'.$value['genus'].'" doesn\'t exists.';
                    $form->addError(new FormError($errorString));

                    $validObjects['species'][$value['genus'].' '.$value['species']] = null;
                } else {
                    $species = $this->em->getRepository('AppBundle:Species')->findOneBy(['genus' => $genus, 'name' => $value['species']]);
                    if (null === $species) {
                        $errorString = 'Line '.($key + 2).', Column "species": The species "'.$value['genus'].' '.$value['species'].'" doesn\'t exists.';
                        $form->addError(new FormError($errorString));
                    }

                    // Add in a array all valid data to prevent some db requests
                    $validObjects['species'][$value['genus'].' '.$value['species']] = $species;
                }
            }
            if (null !== $species = $validObjects['species'][$value['genus'].' '.$value['species']]) {
                $strain->setSpecies($species);
            }

            // Check type
            //Before do a Doctrine query, check if we already have validate it
            if (!array_key_exists($value['type'], $validObjects['type'])) {
                $type = $this->em->getRepository('AppBundle:Type')->findOneBy(['team' => $team, 'name' => $value['type']]);
                if (null === $type) {
                    $errorString = 'Line '.($key + 2).', Column "type": The type "'.$value['type'].'" doesn\'t exists for the team: "'.$team->getName().'".';
                    $form->addError(new FormError($errorString));
                }

                // Add in a array all valid data to prevent some db requests
                $validObjects['type'][$value['type']] = $type;
            }
            if (null !== $type = $validObjects['type'][$value['type']]) {
                $strain->setType($type);
            }

            // Check category
            //Before do a Doctrine query, check if we already have validate it
            if (!empty($value['biologicalOriginCategory'])) {
                if (!array_key_exists($value['biologicalOriginCategory'], $validObjects['category'])) {
                    $category = $this->em->getRepository('AppBundle:BiologicalOriginCategory')->findOneBy(['team' => $team, 'name' => $value['biologicalOriginCategory']]);
                    if (null === $category) {
                        $errorString = 'Line '.($key + 2).', Column "biologicalOriginCategory": The category "'.$value['biologicalOriginCategory'].'" doesn\'t exists for the team: "'.$team->getName().'".';
                        $form->addError(new FormError($errorString));
                    }

                    // Add in a array all valid data to prevent some db requests
                    $validObjects['category'][$value['biologicalOriginCategory']] = $category;
                }
                if (null !== $biologicalOriginCategory = $validObjects['category'][$value['biologicalOriginCategory']]) {
                    $strain->setBiologicalOriginCategory($biologicalOriginCategory);
                }
            }

            // Validate the Strain object with the Validator
            $strainErrors = $this->validator->validate($strain);
            if (count($strainErrors) > 0) {
                $errors[] = $strainErrors;
                foreach ($strainErrors as $error) {
                    $errorString = 'Line '.($key + 2).', Column "'.$error->getPropertyPath().'": '.$error->getMessage();

                    $form->addError(new FormError($errorString));
                }
            }

            if ($form->isValid()) {
                // Persist the strain
                $this->em->persist($strain);
            }
        }

        // If there is no error, flush
        if ($form->isValid()) {
            // Flush all
            $this->em->flush();
        }

        return $form;
    }
}
