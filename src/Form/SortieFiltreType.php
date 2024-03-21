<?php

namespace App\Form;

use App\Entity\Site;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFiltreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod('GET')
            ->add('site', EntityType::class, [
                'label'=>'Site :',
                'class' => Site::class,
                'choice_label' => 'name',
                'placeholder' => ' --Choisir un site-- ',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])


            ->add('search', TextType::class, [
                'label' => 'Le nom de la sortie contient :',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('start_date', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Entre',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('end_date', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'et',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
            ])
            ->add('organisateur', CheckboxType::class, [
                'label' => 'Sorties dont je suis l\'organisateur/trice',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('inscrit', CheckboxType::class, [
                'label' => 'Sorties auxquelles je suis inscrit/e',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('non_inscrit', CheckboxType::class, [
                'label' => 'Sorties auxquelles je ne suis pas inscrit/e',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('state', CheckboxType::class, [
                'label' => 'Sorties passées',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('rechercher', SubmitType::class, [
                'label' => 'Rechercher',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
