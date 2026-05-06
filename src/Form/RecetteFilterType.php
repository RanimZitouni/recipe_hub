<?php

namespace App\Form;

use App\Entity\CategorieRecette;
use App\Entity\TagRecette;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecetteFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', SearchType::class, [
                'required' => false,
                'label' => 'Titre',
            ])
            ->add('categorie', EntityType::class, [
                'class' => CategorieRecette::class,
                'choice_label' => 'nom',
                'required' => false,
                'placeholder' => 'Toutes',
                'label' => 'Catégorie',
            ])
            ->add('difficulte', ChoiceType::class, [
                'choices' => [
                    'Facile' => 'facile',
                    'Moyen' => 'moyen',
                    'Difficile' => 'difficile',
                ],
                'required' => false,
                'placeholder' => 'Toutes',
                'label' => 'Difficulté',
            ])
            ->add('tag', EntityType::class, [
                'class' => TagRecette::class,
                'choice_label' => 'nom',
                'required' => false,
                'placeholder' => 'Tous',
                'label' => 'Tag',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
