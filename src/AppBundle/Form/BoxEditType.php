<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BoxEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('project')
            ->remove('colNumber')
            ->remove('rowNumber')
        ;
    }

    public function getParent()
    {
        return BoxType::class;
    }
}
