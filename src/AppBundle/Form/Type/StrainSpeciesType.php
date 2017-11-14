<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Genus;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StrainSpeciesType extends AbstractType
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $species = $event->getData();

        // For the species field
        $genus = $species ? $species->getGenus() : null;
        $this->addSpeciesElements($form, $genus);
    }

    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        // If the user want select a species
        if (isset($data['genus'])) {
            $genus = $this->em->getRepository('AppBundle:Genus')->findOneById($data['genus']);
            $this->addSpeciesElements($form, $genus);
        }
    }

    protected function addSpeciesElements(FormInterface $form, Genus $genus = null)
    {
        $form->add('genus', EntityType::class, [
            'class' => 'AppBundle\Entity\Genus',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('genus')
                    ->orderBy('genus.name', 'ASC');
            },
            'choice_label' => 'name',
            'placeholder' => '-- select a genus --',
            'mapped' => false,
            'required' => true,
            'data' => $genus,
        ]);

        $form->add('species', EntityType::class, [
            'class' => 'AppBundle\Entity\Species',
            'query_builder' => function (EntityRepository $er) use ($genus) {
                return $er->createQueryBuilder('species')
                    ->where('species.genus = :genus')
                    ->setParameter('genus', $genus)
                    ->orderBy('species.name', 'ASC');
            },
            'placeholder' => '-- select a species --',
            'choice_label' => 'name',
            'label' => 'Species',
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            //'data_class' => 'AppBundle\Entity\Species',
            'inherit_data' => true,
        ]);
    }
}
