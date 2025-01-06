<?php

namespace App\Form;

use App\Entity\User;
use App\Validator\Constraints\PasswordMatch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'utilisateur',
                'attr' => [
                    'placeholder' => 'Nom',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Ce champ ne peut pas être vide.']),
                    new Assert\Length([
                        'min' => 8,
                        'minMessage' => 'Le nom doit comporter au moins {{ limit }} caractères.',
                        'max' => 250,
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÖØ-öø-ÿ\s]+$/',
                        'message' => 'Le nom ne doit contenir que des lettres et des espaces.',
                    ]),
                ],
                'required' => false
            ])

            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Email',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'email ne peut pas être vide.']),
                    new Assert\Email([
                        'message' => 'L\'email « {{ value }} » n\'est pas valide.',
                        'mode' => 'strict',
                    ]),
                ],
                'required' => false
            ])

            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'attr' => [
                    'placeholder' => 'Mot de passe',
                    'class' => 'form-control'
                ],
                'required' => false,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Ce champ est obligatoire.']),
                    new Assert\Length([
                        'min' => 8,
                        'minMessage' => 'Le mot de passe doit comporter au moins {{ limit }} caractères.',
                        'max' => 250,
                        'maxMessage' => 'Le mot de passe ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/',
                        'message' => 'Le mot de passe doit contenir au moins une majuscule, un chiffre et un caractère spécial.',
                    ]),
                ]
            ])
            ->add('confirmPassword', PasswordType::class, [
                'label' => 'Confirmer le mot de passe',
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'Confirmer le mot de passe',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Ce champ est obligatoire.']),
                    new PasswordMatch(),
                ],
                'required' => false
            ])
            ->add('deliveryAddress', TextType::class, [
                'label' => 'Adresse de livraison',
                'attr' => [
                    'placeholder' => 'Adresse',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^[\p{L}0-9\s,.-]*$/u',
                        'message' => 'L\'adresse ne doit contenir que des lettres, des chiffres, des espaces et les caractères suivants : virgule, point et hífen.',
                    ]),
                    new Assert\Length([
                        'min' => 5,
                        'max' => 255,
                        'minMessage' => 'L\'adresse doit comporter au moins {{ limit }} caractères.',
                        'maxMessage' => 'L\'adresse ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
                'required' => false
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Créer un compte',
                'attr' => [
                    'class' => 'btn btn-outline-primary rounded-pill mt-3 mb-3',
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
