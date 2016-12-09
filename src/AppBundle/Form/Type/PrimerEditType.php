<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PrimerEditType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('team')
        ;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return PrimerType::class;
    }
}
