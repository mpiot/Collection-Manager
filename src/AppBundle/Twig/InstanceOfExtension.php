<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Plasmid;
use AppBundle\Entity\Primer;
use AppBundle\Entity\Strain;

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
        ];
    }
}
