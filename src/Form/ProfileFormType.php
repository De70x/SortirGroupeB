<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\User;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur'
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prenom'
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone'
            ])
            ->add('mail', TextType::class, [
                'label' => 'Email'
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'les deux mots de passes doivent être identique !',
                'required' => true,
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Répéter le mot de passe']
            ])
            ->add('site', EntityType::class, [
                'class' => 'App\Entity\Site',
                'query_builder' => function (SiteRepository $siteRepository) {
                    return $siteRepository->createQueryBuilder('site')->orderBy('site.nom', 'ASC');
                },
                'choice_label' => function (Site $site) {
                    return $site->getNom();
                }
            ])
            ->add('photoFile', FileType::class, [
                'label' => 'Photo profil utilisateur',
                'mapped' => false,
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
