<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Plasmid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\RouterInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;

class PlasmidEditType extends AbstractType
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('group')
            ->add('genBankFile', VichFileType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_uri' => function (Plasmid $plasmid) {
                    return $this->router->generate('plasmid_download', ['id' => $plasmid->getId(), 'slug' => $plasmid->getSlug()]);
                },
                'download_label' => function (Plasmid $plasmid) {
                    if (null !== $plasmid->getGenBankName()) {
                        return $plasmid->getAutoName().'_'.$plasmid->getSlug().'.'.pathinfo($plasmid->getGenBankName())['extension'];
                    } else {
                        return null;
                    }
                },
            ])
        ;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return PlasmidType::class;
    }
}
