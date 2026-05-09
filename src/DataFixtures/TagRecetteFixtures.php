<?php
namespace App\DataFixtures;

use App\Entity\TagRecette;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TagRecetteFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tags = [
            ['vegetarien', 'Végétarien', '#4CAF50'],
            ['vegan', 'Végan', '#2E7D32'],
            ['sans_gluten', 'Sans Gluten', '#FF9800'],
            ['bio', 'Bio', '#8BC34A'],
            ['rapide', 'Rapide', '#FF5722'],
            ['familial', 'Familial', '#2196F3'],
            ['festif', 'Festif', '#9C27B0'],
            ['economique', 'Économique', '#FFC107'],
        ];

        foreach ($tags as [$key, $nom, $couleur]) {
            $tag = new TagRecette();
            $tag->setNom($nom);
            $tag->setCouleur($couleur);
            $manager->persist($tag);
            $this->addReference('tag_' . $key, $tag);
        }

        $manager->flush();
    }
}

