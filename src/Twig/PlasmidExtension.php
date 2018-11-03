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

namespace App\Twig;

class PlasmidExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('linearPlasmid', [$this, 'linearPlasmid']),
            new \Twig_SimpleFilter('circularPlasmid', [$this, 'circularPlasmid']),
        ];
    }

    public function linearPlasmid($genBankArray)
    {
        // Some var to config the svg
        $height = 250;
        $width = 800;
        $paddingV = 20;
        $paddingH = 50;

        // Arrow color and size
        $arrowLineWidth = 10;
        $arrowWidth = 14;
        $arrowLength = 10;
        $arrowFill = '#FFBBBB';
        $arrowStroke = '#DD2222';
        $arrowStrokeWidth = 2;

        // Plasmid color and size
        $plasmidStroke = 'black';
        $plasmidStrokeWidth = 1;

        // Define the plasmid length and the gap
        $plasmidLength = $genBankArray['length'];
        $gap = $width / $plasmidLength;

        // Create the SVG
        $svg = '<svg height="'.($height + $paddingV).'" width="'.($width + $paddingH).'">';
        // Add the plasmid line
        $svg .= '<line x1="'.($paddingH / 2).'" y1="'.($height / 2).'" x2="'.($width + $paddingH / 2).'" y2="'.($height / 2).'" style="stroke: '.$plasmidStroke.';stroke-width: '.$plasmidStrokeWidth.'" />';

        // Foreach features in the array
        foreach ($genBankArray['features'] as $key => $feature) {
            // Only draw CDS features
            if ('CDS' === $feature['type']) {
                // Define start and stop position of the arrow
                $x1 = $feature['position']['start'] * $gap + $paddingH / 2;
                $x2 = $feature['position']['stop'] * $gap + $paddingH / 2;

                if (1 === $feature['position']['strand']) {
                    $svg .= '<path 
                        d=" M '.$x1.' '.($height / 2 - $arrowLineWidth / 2).'
                            L '.($x2 - $arrowLength).' '.($height / 2 - $arrowLineWidth / 2).'
                            L '.($x2 - $arrowLength).' '.($height / 2 - $arrowLineWidth / 2 - ($arrowWidth - $arrowLineWidth) / 2).'
                            L '.$x2.' '.($height / 2).'
                            L '.($x2 - $arrowLength).' '.($height / 2 + $arrowLineWidth / 2 + ($arrowWidth - $arrowLineWidth) / 2).'
                            L '.($x2 - $arrowLength).' '.($height / 2 + $arrowLineWidth / 2).'
                            L '.$x1.' '.($height / 2 + $arrowLineWidth / 2).'
                            Z
                            " 
                      
                        stroke="'.$arrowStroke.'" 
                        stroke-width="'.$arrowStrokeWidth.'"px 
                        fill="'.$arrowFill.'" />';
                } else {
                    $svg .= '<path 
                        d=" M '.$x2.' '.($height / 2 - $arrowLineWidth / 2).'
                            L '.($x1 + $arrowLength).' '.($height / 2 - $arrowLineWidth / 2).'
                            L '.($x1 + $arrowLength).' '.($height / 2 - $arrowLineWidth / 2 - ($arrowWidth - $arrowLineWidth) / 2).'
                            L '.$x1.' '.($height / 2).'
                            L '.($x1 + $arrowLength).' '.($height / 2 + $arrowLineWidth / 2 + ($arrowWidth - $arrowLineWidth) / 2).'
                            L '.($x1 + $arrowLength).' '.($height / 2 + $arrowLineWidth / 2).'
                            L '.$x2.' '.($height / 2 + $arrowLineWidth / 2).'
                            Z
                            " 
                      
                        stroke="'.$arrowStroke.'" 
                        stroke-width="'.$arrowStrokeWidth.'"px 
                        fill="'.$arrowFill.'" />';
                }

                $svg .= '<text x="'.(($x1 + $x2) / 2).'" y="'.($height / 2 + $arrowWidth + 5).'" style="text-anchor: middle;">'.$feature['gene'].'</text>';
            }
        }

        // Some informations about the plasmid: name and size
        $svg .= '<text x="'.($width / 2).'" y="'.($height - 10).'" style="text-anchor: middle;">'.$genBankArray['name'].'</text>';
        $svg .= '<text x="'.($width / 2).'" y="'.($height + 10).'" style="text-anchor: middle;">'.$plasmidLength.' bp</text>';

        // Close and return the svg
        $svg .= '</svg>';
        echo  $svg;
    }

    public function circularPlasmid($genBankArray)
    {
        // Parametres graphiques
        $param = [
            'canvas_width' => 500,
            'canvas_height' => 500,
            'radius_factor' => 0.7,
            'origine' => 90,
            'feature_width' => 10,
            'arrow_width' => 14,
            'arrow_length' => 2,
            'legend_tick' => 10,
            'legend_space' => 5,
            'feature_stroke' => '#DD2222',
            'feature_fill' => '#FFBBBB',
            'feature_stroke_width' => 2,
        ];

        // Cette fonction convertie des angles en coordonnees
        function coordonnees($cx, $cy, $rayon, $angle)
        {
            // calcul de l'angle en radian
            $radian = $angle * M_PI / 180.0;
            // calcul des coordonnees correspondantes
            $coord = [
                'x' => $cx + ($rayon * cos($radian)),
                'y' => $cy + ($rayon * sin($radian)),
            ];

            return $coord;
        }

        // Calcul l'ancrage de la legende
        function legend_anchor($angle)
        {
            if ($angle < 30) {
                return 'middle';
            } elseif ($angle < 150) {
                return 'start';
            } elseif ($angle < 210) {
                return 'middle';
            } elseif ($angle < 330) {
                return 'end';
            }

            return 'middle';
        }

        function add_feature($feature, $length, $cx, $cy, $rayon, $param)
        {
            // La convertion consiste a diviser la longeur total en 360 degres
            $angle_depart = 360 * $feature['position']['start'] / $length - $param['origine'];
            $angle_arrivee = 360 * $feature['position']['stop'] / $length - $param['origine'];

            // Cacule des rayons
            $rayon_1 = $rayon - $param['feature_width'] / 2;
            $rayon_2 = $rayon + $param['feature_width'] / 2;
            $rayon_arrow_1 = $rayon - $param['arrow_width'] / 2;
            $rayon_arrow_2 = $rayon + $param['arrow_width'] / 2;

            // Le calcul des coordonnees depend de l'orientation
            if (1 === $feature['position']['strand']) {
                $angle_base_arrow = $angle_arrivee - $param['arrow_length'];
                // Conversion en coordonnees
                $coord_depart_1 = coordonnees($cx, $cy, $rayon_1, $angle_depart);
                $coord_depart_2 = coordonnees($cx, $cy, $rayon_2, $angle_depart);
                $coord_pointe = coordonnees($cx, $cy, $rayon, $angle_arrivee);
                $coord_base_arrow_1 = coordonnees($cx, $cy, $rayon_1, $angle_base_arrow);
                $coord_base_arrow_2 = coordonnees($cx, $cy, $rayon_2, $angle_base_arrow);
                $coord_max_arrow_1 = coordonnees($cx, $cy, $rayon_arrow_1, $angle_base_arrow);
                $coord_max_arrow_2 = coordonnees($cx, $cy, $rayon_arrow_2, $angle_base_arrow);

                // On determine si il s'agit d'un grand angle ou pas
                $angle_large = 0;
                if (($angle_arrivee - $angle_depart) > 180) {
                    $angle_large = 1;
                }
                $sens_1 = 1; // sens des aiguilles d'une montre
                $sens_2 = 0; // Chemin inverse
                $xaxis = 0; // On applique aucune rotation a l'arc

                echo '<path d="M '.$coord_depart_1['x'].' '.$coord_depart_1['y'].' L '.$coord_depart_2['x'].' '.$coord_depart_2['y']." A $rayon_2 $rayon_2 $xaxis $angle_large $sens_1 ".$coord_base_arrow_2['x'].' '.$coord_base_arrow_2['y'].' L '.$coord_max_arrow_2['x'].' '.$coord_max_arrow_2['y'].' L '.$coord_pointe['x'].' '.$coord_pointe['y'].' L '.$coord_max_arrow_1['x'].' '.$coord_max_arrow_1['y'].' L '.$coord_base_arrow_1['x'].' '.$coord_base_arrow_1['y']." A $rayon_1 $rayon_1 $xaxis $angle_large $sens_2 ".$coord_depart_1['x'].' '.$coord_depart_1['y'].'" stroke="'.$param['feature_stroke'].'" stroke-width="'.$param['feature_stroke_width'].'px" fill="'.$param['feature_fill'].'" />';
            } else {
                $angle_base_arrow = $angle_depart + $param['arrow_length'];
                // Conversion en coordonnees
                $coord_arrivee_1 = coordonnees($cx, $cy, $rayon_1, $angle_arrivee);
                $coord_arrivee_2 = coordonnees($cx, $cy, $rayon_2, $angle_arrivee);
                $coord_pointe = coordonnees($cx, $cy, $rayon, $angle_depart);
                $coord_base_arrow_1 = coordonnees($cx, $cy, $rayon_1, $angle_base_arrow);
                $coord_base_arrow_2 = coordonnees($cx, $cy, $rayon_2, $angle_base_arrow);
                $coord_max_arrow_1 = coordonnees($cx, $cy, $rayon_arrow_1, $angle_base_arrow);
                $coord_max_arrow_2 = coordonnees($cx, $cy, $rayon_arrow_2, $angle_base_arrow);

                // On determine si il s'agit d'un grand angle ou pas
                $angle_large = 0;
                if (($angle_arrivee - $angle_depart) > 180) {
                    $angle_large = 1;
                }
                $sens_1 = 1; // sens des aiguilles d'une montre
                $sens_2 = 0; // Chemin inverse
                $xaxis = 0; // On applique aucune rotation a l'arc

                echo '<path d="M '.$coord_base_arrow_1['x'].' '.$coord_base_arrow_1['y'].' L '.$coord_max_arrow_1['x'].' '.$coord_max_arrow_1['y'].' L '.$coord_pointe['x'].' '.$coord_pointe['y'].' L '.$coord_max_arrow_2['x'].' '.$coord_max_arrow_2['y'].' L '.$coord_base_arrow_2['x'].' '.$coord_base_arrow_2['y']." A $rayon_2 $rayon_2 $xaxis $angle_large $sens_1 ".$coord_arrivee_2['x'].' '.$coord_arrivee_2['y'].' L'.$coord_arrivee_1['x'].' '.$coord_arrivee_1['y']." A $rayon_1 $rayon_1 $xaxis $angle_large $sens_2 ".$coord_base_arrow_1['x'].' '.$coord_base_arrow_1['y'].'" stroke="'.$param['feature_stroke'].'" stroke-width="'.$param['feature_stroke_width'].'px" fill="'.$param['feature_fill'].'" />';
            }

            // Ajout de la legend (nom)
            $angle_legend = $angle_depart + ($angle_arrivee - $angle_depart) / 2;
            $coord_legend_1 = coordonnees($cx, $cy, $rayon_2, $angle_legend);
            $coord_legend_2 = coordonnees($cx, $cy, $rayon_2 + $param['legend_tick'], $angle_legend);
            $coord_legend_3 = coordonnees($cx, $cy, $rayon_2 + $param['legend_tick'] + $param['legend_space'], $angle_legend);
            echo '<path d="M '.$coord_legend_1['x'].' '.$coord_legend_1['y'].' L'.$coord_legend_2['x'].' '.$coord_legend_2['y'].'" stroke="black" stroke-width="1" />';
            $anchor = legend_anchor($angle_legend + $param['origine']);
            echo "<text text-anchor=\"$anchor\" x=\"".$coord_legend_3['x'].'" y="'.$coord_legend_3['y'].'">'.$feature['gene'].'</text>';
        }

        // Fonction principale
        function draw_vector($vector, $param)
        {
            // On crée le canvas de base
            echo '<svg width="'.$param['canvas_width'].'" height="'.$param['canvas_height'].'" version="1.1" baseProfile="full" xmlns="http://www.w3.org/2000/svg">';

            // On crée le cercle vide
            $cx = $param['canvas_width'] / 2; // Coordonnees du centre
            $cy = $param['canvas_height'] / 2;
            $rayon = min($cx, $cy) * $param['radius_factor'];
            echo "<circle cx=\"$cx\" cy=\"$cy\" r=\"$rayon\" stroke=\"black\" stroke-width=\"1\" fill=\"white\" />";

            foreach ($vector['features'] as $feature) {
                if ('CDS' === $feature['type']) {
                    add_feature($feature, $vector['length'], $cx, $cy, $rayon, $param);
                }
            }

            // Some informations about the plasmid: name and size
            echo '<text x="'.($cx).'" y="'.($cy - 10).'" style="text-anchor: middle;">'.$vector['name'].'</text>';
            echo '<text x="'.($cx).'" y="'.($cy + 10).'" style="text-anchor: middle;">'.$vector['length'].' bp</text>';

            echo '</svg>';
        }

        draw_vector($genBankArray, $param);
    }

    public function getName()
    {
        return 'plasmid_extension';
    }
}
