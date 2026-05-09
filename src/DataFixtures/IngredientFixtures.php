<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Ingredient;
use App\Entity\Recette;
use Faker\Factory;

class IngredientFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // assume recettes were added with references recette_0..recette_19
        for ($i = 0; $i < 20; $i++) {
            $recetteRef = 'recette_' . $i;
            if (!$this->hasReference($recetteRef, Recette::class)) {
                continue;
            }
            $recette = $this->getReference($recetteRef, Recette::class);

            $count = $faker->numberBetween(3, 8);
            $commonIngredients = [
                'Farine', 'Sucre', 'Sel', 'Poivre', 'Beurre', 'Lait', 'Oeuf', 'Tomate', 'Oignon', 'Ail',
                'Poulet', 'Boeuf', 'Pomme de terre', 'Carotte', 'Courgette', 'Fromage', 'Citron', 'Huile d\'olive'
            ];

            for ($j = 0; $j < $count; $j++) {
                $ingredient = new Ingredient();
                $name = $faker->randomElement($commonIngredients);
                $quantite = $faker->numberBetween(1,500) . ' ' . $faker->randomElement(['g','ml','cl','pcs','cuillères']);
                $ingredient->setNom($name);
                $ingredient->setQuantite($quantite);
                $ingredient->setRecette($recette);
                $manager->persist($ingredient);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            'App\\DataFixtures\\RecetteFixtures',
        ];
    }
}
