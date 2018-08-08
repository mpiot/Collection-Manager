<?php

namespace App\Twig;

use App\Entity\Equipment;
use App\Entity\Plasmid;
use App\Entity\Primer;
use App\Entity\Product;
use App\Entity\Strain;

class InstanceOfExtension extends \Twig_Extension
{
    public function getTests()
    {
        return [
            new \Twig_SimpleTest('primer', function ($event) {
                return $event instanceof Primer;
            }),
            new \Twig_SimpleTest('plasmid', function ($event) {
                return $event instanceof Plasmid;
            }),
            new \Twig_SimpleTest('strain', function ($event) {
                return $event instanceof Strain;
            }),
            new \Twig_SimpleTest('product', function ($event) {
                return $event instanceof Product;
            }),
            new \Twig_SimpleTest('equipment', function ($event) {
                return $event instanceof Equipment;
            }),
        ];
    }
}
