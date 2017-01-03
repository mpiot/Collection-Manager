<?php

namespace AppBundle\Form\Type;

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
            ->add('deleted');
    }

    public function getParent()
    {
        return BoxType::class;
    }
}
