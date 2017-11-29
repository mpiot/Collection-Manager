<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Seller;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\RouterInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;

class SellerEditType extends AbstractType
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
            ->add('offerFile', VichFileType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_uri' => function (Seller $seller) {
                    return $this->router->generate('seller_download_offer', ['slug' => $seller->getSlug()]);
                },
                'download_label' => function (Seller $seller) {
                    if (null !== $seller->getOfferName()) {
                        return $seller->getSlug().'.'.pathinfo($seller->getOfferName())['extension'];
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
        return SellerType::class;
    }
}
