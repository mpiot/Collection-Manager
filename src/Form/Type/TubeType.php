<?php

namespace App\Form\Type;

use App\Entity\Box;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TubeType extends AbstractType
{
    private $em;
    private $tokenStorage;
    private $previousTubes = [];

    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->em = $entityManager;
        $this->tokenStorage = $tokenStorage;
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
        $data = $event->getData();

        $box = null === $data ? null : $data->getBox();
        $cell = null === $data ? null : $data->getCell();

        if (null !== $data) {
            $this->previousTubes[] = $data;
        }

        // Add forms
        $this->addBoxForm($form);
        $this->addCellForm($form, $box, $cell);
    }

    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        $persistedTube = $event->getForm()->getData();

        $box = null === $data['box'] ? null : $this->em->getRepository('App:Box')->findOneById($data['box']);
        $cell = null;

        if (false !== $key = array_search($persistedTube, $this->previousTubes, 1)) {
            $previousTube = $this->previousTubes[$key];

            if ($box === $previousTube->getBox()) {
                $cell = $previousTube->getCell();
            }
        }

        $this->addBoxForm($form);
        $this->addCellForm($form, $box, $cell);
    }

    protected function addBoxForm(FormInterface $form)
    {
        $form->add('box', EntityType::class, [
            'class' => 'App:Box',
            'placeholder' => '-- select a box --',
            'query_builder' => function (EntityRepository $er) use ($form) {
                return $er->createQueryBuilder('box')
                    ->leftJoin('box.group', 'g')
                    ->leftJoin('g.members', 'user')
                    ->where('user = :user')
                    ->andWhere('g = :group')
                        ->setParameters([
                            'user' => $this->tokenStorage->getToken()->getUser(),
                            'group' => $form->getConfig()->getOption('parent_data'),
                        ]);
            },
            'choice_label' => function (Box $box) {
                return (0 === $box->getFreeSpace()) ? $box->getName().' (full)' : $box->getName();
            },
            'choice_attr' => function (Box $box) {
                return (0 === $box->getFreeSpace()) ? ['disabled' => 'disabled'] : [];
            },
            'auto_initialize' => false,
        ]);
    }

    protected function addCellForm(FormInterface $form, Box $box = null, $previousCell = null)
    {
        $cells = null === $box ? null : $box->getEmptyCells($previousCell);

        $form->add('cell', ChoiceType::class, [
            'choices' => $cells,
            'placeholder' => '-- select a cell --',
            'auto_initialize' => false,
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Tube',
        ]);

        $resolver->setRequired(['parent_data']);
    }
}
