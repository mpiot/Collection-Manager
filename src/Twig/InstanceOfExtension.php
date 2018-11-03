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
