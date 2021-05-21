<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'placeholder' => 'Ex : News du jour'
                ],
                'constraints' => [
                    new Length([
                        'min' => 2,
                        'max' => 150,
                        'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le titre peut contenir au maximum {{ limit }} caractères',
                    ]),
                    new NotBlank([
                        'message' => 'Merci de renseigner un titre',
                    ])
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'placeholder' => 'Ex : Bonjour, ...',
                    'rows' => '10'
                ],
                'constraints' => [
                    new Length([
                        'min' => 10,
                        'max' => 25000,
                        'minMessage' => 'Le contenu de l\'article doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le contenu de l\'article peut contenir au maximum {{ limit }} caractères',
                    ]),
                    new NotBlank([
                        'message' => 'Merci de renseigner du contenu',
                    ])
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Créer un article',
                'attr' => [
                    'class' => 'btn btn-primary col-12'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            // TODO: à enlever à la fin
            'attr' => [
                'novalidate' => 'novalidate'
            ]
        ]);
    }
}
