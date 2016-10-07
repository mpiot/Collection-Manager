<?php
namespace AppBundle\Twig;


class PlasmidExtension extends \Twig_Extension {
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('linearPlasmid', array($this, 'linearPlasmid')),
            new \Twig_SimpleFilter('circularPlasmid', array($this, 'circularPlasmid')),
        );
    }

    public function countryFilter($countryCode,$locale = "en"){
        $c = \Symfony\Component\Intl\Intl::getRegionBundle()->getCountryNames($locale);

        return array_key_exists($countryCode, $c)
            ? $c[$countryCode]
            : $countryCode;
    }

    public function linearPlasmid($genBankArray)
    {
        // Some var to config the svg
        $height = 250;
        $width = 800;
        $paddingV = 20;
        $paddingH = 50;

        // Plasmid color and width
        $plasmidStroke = 'black';
        $plasmidStrokeWidth = 1;

        // CDS color and width
        $cdsColor = 'red';
        $cdsWidth = 5;

        // Define the plasmid length and the gap
        $plasmidLength = strlen($genBankArray['fasta']);
        $gap = $width / $plasmidLength;

        // Create the SVG
        $svg = '<svg height="'.($height + $paddingV).'" width="'.($width + $paddingH).'">';
        // Add the plasmid line
        $svg .= '<line x1="'.($paddingH/2).'" y1="'.($height/2).'" x2="'.($width + $paddingH/2).'" y2="'.($height/2).'" style="stroke: '.$plasmidStroke.';stroke-width: '.$plasmidStrokeWidth.'" />';

        // Foreach features in the array
        foreach ($genBankArray['features'] as $key => $feature)
        {
            // Only draw CDS features
            if ('CDS' === $feature['type']) {
                // Define start and stop position of the arrow
                $x1 = $feature['position']['start'] * $gap + $paddingH/2;
                $x2 = $feature['position']['stop'] * $gap + $paddingH/2;

                // Draw the end of the arraw
                $svg .= '<defs>';
                $svg .= '<marker id="arrowhead'.$key.'" markerWidth="4" markerHeight="5"
                            refX="0" refY="1.5" orient="auto" style="fill: '.$cdsColor.';">
                            <polygon points="0 0, 4 1.5, 0 3" />
                        </marker>';
                $svg .= '</defs>';
                // Draw the arrow line
                $svg .= '<line x1="'.$x1.'" y1="'.($height/2).'" x2="'.$x2.'" y2="'.($height/2).'" style="stroke: '.$cdsColor.'; stroke-width:'.$cdsWidth.'" marker-end="url(#arrowhead'.$key.')" />';
                // Add a text to explain wich CDS it is
                $svg .= '<text x="'.(($x1+$x2) / 2).'" y="'.($height/2 + 12 + 5).'" style="text-anchor: middle;">';
                if (isset($feature['note'])) {
                    $svg .= $feature['note'][0];
                } else {
                    $svg .= 'No note';
                }
                $svg .= '</text>';
            }
        }

        // Some informations about the plasmid: name and size
        $svg .= '<text x="'.($width/2).'" y="'.($height - 10).'" style="text-anchor: middle;">'.$genBankArray['name'].'</text>';
        $svg .= '<text x="'.($width/2).'" y="'.($height + 10).'" style="text-anchor: middle;">'.$plasmidLength.' bp</text>';

        // Close and return the svg
        $svg .= '</svg>';
        echo  $svg;
    }

    public function circularPlasmid($genBankArray)
    {

    }

    public function getName()
    {
        return 'plasmid_extension';
    }
}
