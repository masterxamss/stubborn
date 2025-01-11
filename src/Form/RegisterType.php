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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use function pcov\waiting;

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

            ->add('deliveryAddress', FormType::class, [
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'adresse de livraison est obligatoire.']),
                ]
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Créer un compte',
                'attr' => [
                    'class' => 'btn btn-outline-primary rounded-pill mt-3 mb-3',
                ]
            ])

            ->get('deliveryAddress')
            ->add('street', TextType::class, [
                'label' => 'Rue',
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^[\p{L}0-9\s,.-]*$/u',
                        'message' => 'La rue ne doit contenir que des lettres, des chiffres, des espaces et les caractères suivants : virgule, point et hífen.',
                    ]),
                    new Assert\Length([
                        'min' => 5,
                        'max' => 255,
                        'minMessage' => 'La rue doit comporter au moins {{ limit }} caractères.',
                        'maxMessage' => 'La rue ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                    new Assert\NotBlank(['message' => 'Ce champ est obligatoire.']),
                ],
                'required' => false
            ])

            ->add('city', TextType::class, [
                'label' => 'Ville',
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^[\p{L}0-9\s,.-]*$/u',
                        'message' => 'La ville ne doit contenir que des lettres, des chiffres, des espaces et les caractères suivants : virgule, point et hífen.',
                    ]),
                    new Assert\Length([
                        'min' => 5,
                        'max' => 255,
                        'minMessage' => 'La ville doit comporter au moins {{ limit }} caractères.',
                        'maxMessage' => 'La ville ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                    new Assert\NotBlank(['message' => 'Ce champ est obligatoire.']),
                ],
                'required' => false
            ])

            ->add('zipCode', TextType::class, [
                'label' => 'Code postal',
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^(?:[A-Z]{1,2}[0-9][A-Z0-9]? ?[0-9][A-Z]{2}|[0-9]{5})$/i',
                        'message' => 'Le code postal doit être valide pour le Royaume-Uni ou la France.',
                    ]),
                    new Assert\Length([
                        'min' => 5,
                        'max' => 255,
                        'minMessage' => 'Le code postal doit comporter au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le code postal ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                    new Assert\NotBlank(['message' => 'Ce champ est obligatoire.']),
                ],
                'required' => false
            ])

            ->add('state', TextType::class, [
                'label' => 'Region',
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^[\p{L}0-9\s,.-]*$/u',
                        'message' => 'La region ne doit contenir que des lettres, des chiffres, des espaces et les caractères suivants : virgule, point et hífen.',
                    ]),
                    new Assert\Length([
                        'min' => 5,
                        'max' => 255,
                        'minMessage' => 'La region doit comporter au moins {{ limit }} caractères.',
                        'maxMessage' => 'La region ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                    new Assert\NotBlank(['message' => 'Ce champ est obligatoire.']),
                ],
                'required' => false
            ])

            ->add('country', ChoiceType::class, [
                'choices' => [
                    'France' => 'FR',
                    'United Kingdom' => 'GB',
                ],
                'label' => 'Pays',
                'placeholder' => 'Choose a country',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Ce champ est obligatoire.']),
                ],
                'required' => false, // Torna o campo obrigatório
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
