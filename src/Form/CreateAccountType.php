<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [ 
                'label' => "Votre email",
                'row_attr' => [
                    'class' => 'form-group'
                ]
            ])
            ->add('password', RepeatedType::class, [
                'label' => "Votre mot de passe",
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'row_attr' => [
                    'class' => 'form-group',
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Répéter le mot de passe']
                ]
            ])
            ->add('name', TextType::class, [ 
                'label' => "Votre pseudo",
                'row_attr' => [
                    'class' => 'form-group'
                ]
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
