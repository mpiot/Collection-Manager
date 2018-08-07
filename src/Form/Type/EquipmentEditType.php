<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EquipmentEditType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('group');
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return EquipmentType::class;
    }
}
