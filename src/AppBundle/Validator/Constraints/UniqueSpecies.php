<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
* @Annotation
*/
class UniqueSpecies extends Constraint
{
    public $message = 'This species already exists.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
