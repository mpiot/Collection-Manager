<?php

namespace AppBundle\Utils;

use AppBundle\Entity\Box;
use AppBundle\Entity\Project;

class CSVExporter
{
    public function exportBox(Box $box)
    {
        $handle = fopen('php://output', 'w+');

        fputcsv($handle, ['autoName', 'name', 'box', 'cell'],';');

        foreach($box->getTubes() as $tube) {
            fputcsv(
                $handle,
                [
                    $tube->getStrain()->getAutoName(),
                    $tube->getStrain()->getName(),
                    $tube->getBox()->getName(),
                    $tube->getCellName(),
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
