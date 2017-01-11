<?php

namespace AppBundle\Validator\Constraints;

use AppBundle\Entity\Species;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueSpeciesValidator extends ConstraintValidator
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function validate($species, Constraint $constraint)
    {
        $genus = $species->getGenus();
        $speciesList = $this->em->getRepository('AppBundle:Species')->findByGenus($genus);

        foreach ($speciesList as $speciesGenus) {
            if ($species->getName() === $speciesGenus->getName()) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('name')
                    ->addViolation();

                break;
            }
        }
    }
}
