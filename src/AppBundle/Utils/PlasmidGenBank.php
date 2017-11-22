<?php

namespace AppBundle\Utils;

use Symfony\Component\Finder\Finder;
use AppBundle\Entity\Plasmid;

class PlasmidGenBank
{
    private $plasmid;
    private $finder;

    public function __construct(Plasmid $plasmid)
    {
        $this->plasmid = $plasmid;
        $this->finder = new Finder();
    }

    public function getFile()
    {
        $file = null;

        if (null !== $this->plasmid->getGenBankName()) {
            // In finder do .. to leave the web folder
            $this->finder->in('../files/plasmids')->files()->name($this->plasmid->getGenBankName());

            foreach ($this->finder as $file) {
                $file = $file->getContents();
            }
        }

        return $file;
    }

    public function getArray()
    {
        if (null === $this->getFile()) {
            return;
        }

        $lines = explode("\n", $this->getFile());

        $array = [];
        $array['name'] = $this->plasmid->getAutoName().' - '.$this->plasmid->getName();

        $i = 0;

        foreach ($lines as $line) {
            // First we want Features data, they starts by 5 spaces
            //if (preg_match('/^ {5}([\w]+) +(?:(complement)\()?(\d+)..(\d+)\)?/', $line, $matches)) {
            if (preg_match('/^ {5}([\w]+) +(?:(?:(complement)\()?(\d+)\.\.(\d+)\)?)/', $line, $matches)) {
                // The feature type: source, misc_feature, promoter, ...
                $array['features'][]['type'] = $matches[1];

                // The position: an array like [sens/reverse, start, end]
                $array['features'][$i]['position'] = [
                    'strand' => ('complement' === $matches[2]) ? -1 : 1,
                    'start' => $matches[3],
                    'stop' => $matches[4],
                ];

                ++$i;
            }

            // In second, we want other informations on features (organism, mol_type, label, gene, translation)
            // all of this depend of the feature type
            // If it's all but no a translation
            elseif (preg_match('/^ {21}\/([a-zA-Z_]+)=(?:"\'?([\w\d\s_.\-\(\)]+)"|(?:(\d)))/', $line, $matches)) {
                if ('codon_start' === $matches[1]) {
                    $array['features'][$i - 1]['codon_start'] = $matches[3];
                } elseif ('note' === $matches[1]) {
                    $array['features'][$i - 1]['note'][] = $matches[2];
                } else {
                    $array['features'][$i - 1][$matches[1]] = $matches[2];
                }
            }
            /*
                        // If it's a translation
                        elseif (preg_match('/ {21}(?:\/translation=")?([A-Z]+)/', $line, $matches)) {
                            if (array_key_exists('translation', $array['features'][$i - 1])) {
                                $array['features'][$i - 1]['translation'] .= $matches[1];
                            } else {
                                $array['features'][$i - 1]['translation'] = $matches[1];
                            }
                        }
            */

            // Finally, if it the sequence
            elseif (preg_match('/^ +\d+ ([\w ]+)/', $line, $matches)) {
                $fasta = preg_replace('/ |\d|\n/', '', $matches[1]);

                if (array_key_exists('fasta', $array)) {
                    $array['fasta'] .= $fasta;
                } else {
                    $array['fasta'] = $fasta;
                }
            }
        }

        $array['length'] = array_key_exists('fasta', $array) ? strlen($array['fasta']) : null;

        return $array;
    }
}
