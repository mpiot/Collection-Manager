<?php

namespace AppBundle\Twig;


use AppBundle\Entity\GmoStrain;
use AppBundle\Entity\Plasmid;
use AppBundle\Entity\Primer;
use AppBundle\Entity\WildStrain;

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
            new \Twig_SimpleTest('gmoStrain', function ($event) {
                return $event instanceof GmoStrain;
            }),
            new \Twig_SimpleTest('wildStrain', function ($event) {
                return $event instanceof WildStrain;
            })
        ];
    }
}
