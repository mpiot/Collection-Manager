<?php

namespace AppBundle\Utils;

use AppBundle\Entity\Box;

class CSVExporter
{
    public function exportBox(Box $box)
    {
        $handle = fopen('php://output', 'w+');

        fputcsv($handle, ['autoName', 'name', 'box', 'cell', 'species', 'comment', 'sequenced', 'description', 'genotype', 'plasmids', 'parents', 'biologicalOrigin', 'source', 'lat', 'long', 'address', 'country'], ';');

        foreach ($box->getTubes() as $tube) {
            fputcsv(
                $handle,
                [
                    $tube->getStrain()->getAutoName(),
                    $tube->getStrain()->getName(),
                    $tube->getBox()->getName(),
                    $tube->getCellName(),
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
                    $tube->getStrain()->getCountry(),
                ],
                ';'
            );
        }

        fclose($handle);

        return $handle;
    }
}
