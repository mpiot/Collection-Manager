<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class BoxImportType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('csvFile', FileType::class, [
                'constraints' => [
                    new File([
                        'mimeTypes' => ['text/csv', 'text/plain'],
                    ]),
                ],
            ])
        ;
    }
}
