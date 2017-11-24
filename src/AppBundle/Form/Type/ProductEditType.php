<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\RouterInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;

class ProductEditType extends AbstractType
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
            ->add('quoteFile', VichFileType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_uri' => function (Product $product) {
                    return $this->router->generate('product_download_quote', ['id' => $product->getId()]);
                },
                'download_label' => function (Product $product) {
                    if (null !== $product->getQuoteName()) {
                        return $product->getSlug().'-quote.'.pathinfo($product->getQuoteName())['extension'];
                    } else {
                        return null;
                    }
                },
            ])
            ->add('manualFile', VichFileType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_uri' => function (Product $product) {
                    return $this->router->generate('product_download_manual', ['id' => $product->getId()]);
                },
                'download_label' => function (Product $product) {
                    if (null !== $product->getManualName()) {
                        return $product->getSlug().'-manual.'.pathinfo($product->getManualName())['extension'];
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
        return ProductType::class;
    }
}
