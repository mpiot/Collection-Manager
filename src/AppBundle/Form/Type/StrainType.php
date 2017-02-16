<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Genus;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class StrainType extends AbstractType
{
    private $em;
    protected $tokenStorage;

    public function __construct(EntityManager $em, TokenStorage $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', EntityType::class, [
                'class' => 'AppBundle\Entity\Type',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('types')
                        ->leftJoin('types.team', 'team')
                        ->leftJoin('team.members', 'members')
                        ->where('members = :user')
                            ->setParameter('user', $this->tokenStorage->getToken()->getUser())
                        ->orderBy('types.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => '-- select a type --',
                'attr' => [
                    'data-help' => 'Which type of organism is it ?',
                ],
            ])
            ->add('name', TextType::class, [
                'attr' => [
                    'autocomplete' => 'off',
                    'data-help' => 'The name you want use to communicate about this strain.',
                ],
            ])
            ->add('comment')
            ->add('sequenced')
            ->add('deleted')
            ->add('tubes', CollectionType::class, [
                'entry_type' => TubeType::class,
                'allow_add' => true,
                'allow_delete' => true,
                // Use add and remove properties in the entity
                'by_reference' => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
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
        ]);
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $strain = $event->getData();

        // For the species field
        $genus = $strain->getSpecies() ? $strain->getSpecies()->getGenus() : null;
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
}
