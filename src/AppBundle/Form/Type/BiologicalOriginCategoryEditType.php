<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BiologicalOriginCategoryEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('team')
        ;
    }

    public function getParent()
    {
        return BiologicalOriginCategoryType::class;
    }
}
