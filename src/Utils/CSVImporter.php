<?php

/*
 * Copyright 2016-2018 Mathieu Piot.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Utils;

use App\Entity\Box;
use App\Entity\Strain;
use App\Entity\Tube;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CSVImporter
{
    private $em;
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->em = $entityManager;
        $this->validator = $validator;
    }

    public function importBox(Box $box, FormInterface $form)
    {
        $file = $form->get('csvFile')->getData()->getRealPath();
        $keys = ['disc', 'genus', 'species', 'name', 'comment', 'sequenced', 'cells', 'description', 'genotype', 'biologicalOrigin', 'source', 'lat', 'long', 'address', 'country'];
        $data = [];

        $row = 0;
        if (false !== ($handle = fopen($file, 'rb'))) {
            while (false !== ($line = fgetcsv($handle, 1000, ';'))) {
                ++$row;
                if (1 === $row) {
                    continue;
                }

                $data[] = array_combine($keys, $line);
            }
            fclose($handle);
        }

        // Change all empty values by null
        array_walk_recursive($data, function (&$value) {
            $value = trim($value);
            if ('' === $value) {
                $value = null;
            }
        });

        // Prepare an array to save SQL requests
        $validObjects = [
            'species' => [],
        ];

        // Get the empty cells array
        $emptyCells = $box->getEmptyCells();

        foreach ($data as $key => $value) {
            // Create the strain
            $strain = new Strain();

            // Get cells
            if (null === $value['cells']) {
                $errorString = 'Line '.($key + 2).', Column "cells": You must define a cell.';
                $form->addError(new FormError($errorString));
            } else {
                $cells = explode(',', $value['cells']);

                foreach ($cells as $cell) {
                    // Init a tube
                    $tube = new Tube();
                    $tube->setBox($box);
                    if (array_key_exists($cell, $emptyCells)) {
                        $tube->setCell($emptyCells[$cell]);
                        // After set a cell, we remove the cell from the array, and re-organize keys for the next
                        unset($emptyCells[$cell]);
                    } else {
                        $errorString = 'Line '.($key + 2).', Column "cells": The cell '.$cell.' is not available (maybe already used).';
                        $form->addError(new FormError($errorString));
                    }

                    $strain->addTube($tube);
                }
            }

            // Set attributes for the strain
            $strain->setGroup($box->getGroup());
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

            // Check species
            //Before do a Doctrine query, check if we already have validate it
            if (!array_key_exists($value['genus'].' '.$value['species'], $validObjects['species'])) {
                $genus = $this->em->getRepository('App:Genus')->findOneByName($value['genus']);
                if (null === $genus) {
                    $errorString = 'Line '.($key + 2).', Column "genus": The genus "'.$value['genus'].'" doesn\'t exists.';
                    $form->addError(new FormError($errorString));

                    $validObjects['species'][$value['genus'].' '.$value['species']] = null;
                } else {
                    $species = $this->em->getRepository('App:Species')->findOneBy(['genus' => $genus, 'name' => $value['species']]);
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

            // Validate the Strain object with the Validator
            $strainErrors = $this->validator->validate($strain);
            if (\count($strainErrors) > 0) {
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
