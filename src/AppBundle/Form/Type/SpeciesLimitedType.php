<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class SpeciesLimitedType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('taxId', IntegerType::class, [
                'attr' => [
                    'data-help' => 'The taxID of the species, you can find it <a target="_blank" href="https://www.ncbi.nlm.nih.gov/taxonomy">here</a>.',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;
    }
}
