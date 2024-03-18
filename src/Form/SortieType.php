<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

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
                'constraints'=>[
                    new Assert\Callback(function ($object, ExecutionContextInterface $context) {
                        $now = new \DateTime('now');
                        if($object < $now->modify('+1 hour')){
                            $context
                                ->buildViolation('Startactiv must be after now')
                                ->addViolation();
                        }
                    })
                ]
            ])
            ->add('duration')
            ->add('dateInscriptionLimit', null, [
                'label' => 'Date limite d\'inscription : ',
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\Callback(function ($object, ExecutionContextInterface $context) {
                        $start = $context->getRoot()->getData()->getDateTimeStart();
                        $stop = $object;
                        if (is_a($start, \DateTime::class) && is_a($stop, \DateTime::class)) {

                            if($stop >= $start){
                                $context
                                    ->buildViolation('Stopinsc must be before startactiv')
                                    ->addViolation();
                            }
                        }
                    }),
                ],
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
            ->add('site', EntityType::class, [
                'label'=>'Site organisateur : ',
                'class' => Site::class,
                'choice_label' => 'name',
                'placeholder' => ' --Choisir un site-- ',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
