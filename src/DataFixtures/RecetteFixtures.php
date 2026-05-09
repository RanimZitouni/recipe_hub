<?php
namespace App\DataFixtures;

use App\Entity\Recette;
use App\Entity\TagRecette;
use App\Entity\CategorieRecette;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;

/**
 * @method object getReference(string $name)
 * @method bool hasReference(string $name)
 */
class RecetteFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $difficultes = ['facile', 'moyen', 'difficile'];

        // gather tag reference keys
        $tagKeys = [
            'tag_vegetarien', 'tag_vegan', 'tag_sans_gluten', 'tag_bio',
            'tag_rapide', 'tag_familial', 'tag_festif', 'tag_economique'
        ];

        // gather category keys
        $categorieKeys = ['categorie_entree','categorie_plat','categorie_dessert','categorie_boisson','categorie_snack','categorie_soupe'];

        // gather user refs
        $userRefs = ['user_admin','user_chef','user_1','user_2','user_3','user_4','user_5'];

        for ($i = 0; $i < 20; $i++) {
            $recette = new Recette();
            $title = ucfirst($faker->words($faker->numberBetween(2,4), true));
            $recette->setTitre($title);
            $recette->setDescription($faker->paragraph(3));
            $recette->setInstructions($faker->paragraphs(2, true));
            $recette->setTempsPreparation($faker->numberBetween(5,120));
            $recette->setTempsCuisson($faker->optional(0.7)->numberBetween(0,180));
            $recette->setDifficulte($difficultes[array_rand($difficultes)]);
            $recette->setNbPersonnes($faker->numberBetween(1,8));
            $recette->setDateCreation(new \DateTimeImmutable());
            $recette->setPubliee($faker->boolean(70));

            // category
            $catKey = $categorieKeys[array_rand($categorieKeys)];
            $recette->setCategorie($this->getReference($catKey, CategorieRecette::class));

            // author random
            $authorKey = $userRefs[array_rand($userRefs)];
            $recette->setAuteur($this->getReference($authorKey, User::class));

            // tags 1..4
            $nbTags = $faker->numberBetween(1,4);
            $selected = (array)array_rand(array_flip($tagKeys), $nbTags);
            if (!is_array($selected)) {
                $selected = [$selected];
            }
            foreach ($selected as $tKey) {
                $tag = $this->getReference($tKey, TagRecette::class);
                if ($tag instanceof TagRecette) {
                    $recette->addTag($tag);
                }
            }

            $manager->persist($recette);
            $this->addReference('recette_' . $i, $recette);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategorieRecetteFixtures::class,
            TagRecetteFixtures::class,
            UserFixtures::class,
        ];
    }
}

