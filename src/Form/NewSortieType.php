<?php

namespace App\Form;

use App\Entity\Sortie;
use http\Client\Curl\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom',TextType::class,[
                'label'=>'Nom de la sortie'
            ])
            ->add('dateHeureDebut', DateTimeType::class,[
                'label'=>'Date et heure de la sortie'
            ])
            ->add('dateLimiteInscription',DateType::class,[
                'label'=>'Date limite d\'inscription'
            ])
            ->add('nbInscriptionsMax', TextType::class,[
                'label'=>'Nombre de places'
            ])
            ->add('duree', NumberType::class,[
                'label'=>'Durée (minutes)'
            ])
            ->add('infosSortie', TextareaType::class,[
                'label'=>'Description'
            ])
            //->add('lieu', null,[
            //    'label'=>'Lieu',
            //    'choice_label'=>'nom'
            //])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
