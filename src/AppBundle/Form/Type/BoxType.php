<?php

namespace AppBundle\Form\Type;

use AppBundle\Repository\ProjectRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class BoxType extends AbstractType
{
    private $tokenStorage;

    public function __construct(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('project', EntityType::class, [
                'class'         => 'AppBundle\Entity\Project',
                'query_builder' => function (ProjectRepository $pr) {
                    return $pr->createQueryBuilder('project')
                        ->leftJoin('project.members', 'members')
                        ->where('members = :user')
                            ->setParameter('user', $this->tokenStorage->getToken()->getUser())
                        ->orderBy('project.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder'  => '-- select a project --',
            ])
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Name of the box',
                ],
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'Description about the box',
                ],
            ])
            ->add('freezer', TextType::class, [
                'attr' => [
                    'placeholder' => 'Emile',
                    'data-help'   => 'In which freezer is the box ?',
                ],
            ])
            ->add('location', TextType::class, [
                'label' => 'Location in the freezer',
                'attr'  => [
                    'placeholder' => '1st shelf on the top, 3rd rack on the left',
                    'data-help'   => 'Where is the box in the freezer ?',
                ],
            ])
            ->add('colNumber', NumberType::class, [
                'label' => 'Number of columns',
                'attr'  => [
                    'placeholder' => '10',
                ],
            ])
            ->add('rowNumber', NumberType::class, [
                'label' => 'Number of rows',
                'attr'  => [
                    'placeholder' => '10',
                ],
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Box',
        ]);
    }
}
