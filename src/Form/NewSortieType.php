<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use http\Client\Curl\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ->add('dateHeureDebut', TextType::class,[
                'label'=>'Date et heure de la sortie'
            ])
            ->add('dateLimiteInscription', TextType::class,[
                'label'=>'Date limite d\'inscription'
            ])
            ->add('nbInscriptionsMax', NumberType::class,[
                'label'=>'Nombre de places'
            ])
            ->add('duree', NumberType::class,[
                'label'=>'Durée (minutes)'
            ])
            ->add('infosSortie', TextareaType::class,[
                'label'=>'Description'
            ])
            ->add('lieu', EntityType::class,[
                'class'=>Lieu::class,
                'choice_label'=>'nom'])
            ->add('publier', SubmitType::class,[
                'label'=>'Publier',
                'attr'=>[
                    'id'=> 'publier',
                    'class'=>'btn btn-block',
                    'value'=>'publier',
                    'style'=>'background-color: #ff8906; color:#fffffe; outline: none; border-color:none',
                ]
            ])
            ->add('enregistrer', SubmitType::class,[
                'label'=>'Enregistrer',
                'attr'=>[
                    'id'=>'enregistrer',
                    'class'=>'btn btn-block',
                    'value'=>'enregistrer',
                    'style'=>'background-color: #ff8906; color:#fffffe; outline: none, border-color:none'
                ]

    ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
