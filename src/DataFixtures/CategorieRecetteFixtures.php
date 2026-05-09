<?php
namespace App\DataFixtures;

use App\Entity\CategorieRecette;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategorieRecetteFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            ['entree', 'Entrée', '🥗'],
            ['plat', 'Plat', '🍝'],
            ['dessert', 'Dessert', '🍰'],
            ['boisson', 'Boisson', '🥤'],
            ['snack', 'Snack', '🍕'],
            ['soupe', 'Soupe', '🥣'],
        ];

        foreach ($categories as [$key, $nom, $icone]) {
            $cat = new CategorieRecette();
            $cat->setNom($nom);
            $cat->setIcone($icone);
            $manager->persist($cat);
            $this->addReference('categorie_' . $key, $cat);
        }

        $manager->flush();
    }
}

