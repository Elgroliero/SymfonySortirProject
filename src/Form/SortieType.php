<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',null,[
                'label' => 'nom de l\'event : '
            ])
            ->add('dateTimeStart', null, [
                'label' => 'Date de début de l\'évenemeent :',
                'widget' => 'single_text',
            ])
            ->add('duration')
            ->add('dateInscriptionLimit', null, [
                'label' => 'Date limite d\'inscription : ',
                'widget' => 'single_text',
            ])
            ->add('maxInscriptionNb',IntegerType::class,[
               'label' => "Nombre d'inscrits MAX : "
            ])
            ->add('description')
            ->add('lieu', EntityType::class, [
                'label' => 'Lieu : ',
                'class' => Lieu::class,
                'choice_label' => 'name',
                'required' => false,
                'row_attr' => ['class' => 'lieu'],
                'placeholder' => ' --Choisir un lieu-- ',
            ])
            ->add('lieu_name', null, [
                    'label' => 'Nom du lieu : ',
                    'mapped' => false,
                    'required' => false,
                'row_attr' => ['class' => 'lieu_form']
            ])
            ->add('lieu_street', null, [
                'label' => 'Rue : ',
                'mapped' => false,
                'required' => false,
                'row_attr' => ['class' => 'lieu_form']
            ])
            ->add('lieu_lat', null, [
                'label' => 'latitude : ',
                'mapped' => false,
                'required' => false,
                'row_attr' => ['class' => 'lieu_form']
            ])
            ->add('lieu_long', null, [
                'label' => 'longitude : ',
                'mapped' => false,
                'required' => false,
                'row_attr' => ['class' => 'lieu_form']
            ])
            ->add('lieu_city', EntityType::class, [
                'label'=>'Ville : ',
                'class' => Ville::class,
                'choice_label' => 'name',
                'mapped' => false,
                'required' => false,
                'row_attr' => ['class' => 'lieu_form']
            ])
            ->add('siteOrga', EntityType::class, [
                'label'=>'Site organisateur : ',
                'class' => Site::class,
                'choice_label' => 'name',
                'placeholder' => ' --Choisir un site-- ',
            ])
            ->add('submit',SubmitType::class,[])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
