<?php

namespace AppBundle\Utils;

use AppBundle\Entity\Box;
use AppBundle\Entity\Project;

class CSVExporter
{
    public function exportBox(Box $box)
    {
        $handle = fopen('php://output', 'w+');

        fputcsv($handle, ['autoName', 'name', 'box', 'cell', 'species', 'comment', 'sequenced', 'deleted', 'description', 'genotype', 'plasmids', 'parents', 'biologicalOriginCategory', 'biologicalOrigin', 'source', 'lat', 'long', 'address', 'country'], ';');

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
                    $tube->getStrain()->getDeleted() ? 'yes' : 'no',
                    $tube->getStrain()->getDescription(),
                    $tube->getStrain()->getGenotype(),
                    implode(',', $tube->getStrain()->getStrainPlasmids()->toArray()),
                    implode(',', $tube->getStrain()->getParents()->toArray()),
                    null !== $tube->getStrain()->getBiologicalOriginCategory() ? $tube->getStrain()->getBiologicalOriginCategory()->getName() : '',
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

    public function exportProject(Project $project)
    {
        $handle = fopen('php://output', 'w+');

        foreach ($project->getBoxes() as $box) {
            if (!$box->getTubes()->isEmpty()) {
                $this->exportBox($box);
            }
        }

        fclose($handle);
    }
}
