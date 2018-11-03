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
use App\Entity\Plasmid;
use App\Entity\Primer;
use App\Entity\Strain;

class CSVExporter
{
    public function exportBox(Box $box)
    {
        $handle = fopen('php://output', 'w+b');

        fputcsv($handle, ['discr', 'autoName', 'name', 'box', 'cell', 'strain species', 'strain comment', 'strain sequenced', 'strain description', 'strain genotype', 'strain plasmids', 'strain parents list', 'strain biological origin', 'strain source', 'strain latitude', 'strain longitude', 'strain address', 'strain country', 'plasmid primers list', 'primer description', 'primer orientation', 'primer sequence', 'primer 5\' extension', 'primer label marker', 'primer plasmids list', 'primer hybridation temp'], ';');

        foreach ($box->getTubes() as $tube) {
            $outputArray = [
                // Common to: Strains, Plasmids, Primers
                $tube->getContentDiscr(),
                $tube->getContent()->getAutoName(),
                $tube->getContent()->getName(),
                $tube->getBox()->getName(),
                $tube->getCellName(),
            ];

            // Only for Strain
            if ($tube->getContent() instanceof Strain) {
                array_push($outputArray,
                    $tube->getStrain()->getSpecies()->getScientificName(),
                    $tube->getStrain()->getComment(),
                    $tube->getStrain()->getSequenced() ? 'yes' : 'no',
                    $tube->getStrain()->getDescription(),
                    $tube->getStrain()->getGenotype(),
                    implode(',', $tube->getStrain()->getStrainPlasmids()->toArray()),
                    implode(',', $tube->getStrain()->getParents()->toArray()),
                    $tube->getStrain()->getBiologicalOrigin(),
                    $tube->getStrain()->getSource(),
                    $tube->getStrain()->getLatitude(),
                    $tube->getStrain()->getLongitude(),
                    $tube->getStrain()->getAddress(),
                    $tube->getStrain()->getCountry()
                );
            } else {
                array_push($outputArray, '', '', '', '', '', '', '', '', '', '', '', '', '');
            }

            // Only for Plasmids
            if ($tube->getContent() instanceof Plasmid) {
                array_push($outputArray,
                    implode(',', $tube->getPlasmid()->getPrimers()->toArray())
                );
            } else {
                array_push($outputArray, '');
            }

            // Only for Primers
            if ($tube->getContent() instanceof Primer) {
                array_push($outputArray,
                    $tube->getPrimer()->getDescription(),
                    $tube->getPrimer()->getOrientation(),
                    $tube->getPrimer()->getSequence(),
                    $tube->getPrimer()->getFivePrimeExtension(),
                    $tube->getPrimer()->getLabelMarker(),
                    implode(',', $tube->getPrimer()->getPlasmids()->toArray()),
                    $tube->getPrimer()->getHybridationTemp()
                );
            } else {
                array_push($outputArray, '', '', '', '', '', '', '');
            }

            fputcsv($handle, $outputArray, ';');
        }

        fclose($handle);

        return $handle;
    }
}
