<?php
namespace App\DataFixtures;

use App\Entity\CategorieRecette;
use App\Entity\Ingredient;
use App\Entity\Recette;
use App\Entity\TagRecette;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        // User
        $user = new User();
        $user->setEmail('test@recipehub.com');
        $user->setPseudo('TestUser');
        $user->setRoles(['ROLE_CUISINIER']);
        $user->setPassword($this->hasher->hashPassword($user, 'password123'));
        $manager->persist($user);

        // Categorie
        $categorie = new CategorieRecette();
        $categorie->setNom('Italien');
        $categorie->setIcone('🍕');
        $categorie->setDescription('Cuisine italienne');
        $manager->persist($categorie);

        // Tag
        $tag = new TagRecette();
        $tag->setNom('Végétarien');
        $tag->setCouleur('#27ae60');
        $manager->persist($tag);

        // Recette publiée
        $recette1 = new Recette();
        $recette1->setTitre('Pizza Margherita');
        $recette1->setDescription('Une délicieuse pizza italienne classique avec tomate et mozzarella.');
        $recette1->setInstructions('Étape 1: Préparer la pâte. Étape 2: Ajouter la sauce. Étape 3: Cuire.');
        $recette1->setTempsPreparation(30);
        $recette1->setTempsCuisson(20);
        $recette1->setDifficulte('facile');
        $recette1->setNbPersonnes(4);
        $recette1->setPubliee(true);
        $recette1->setCategorie($categorie);
        $recette1->setAuteur($user);
        $recette1->addTag($tag);
        $manager->persist($recette1);

        // Ingrédient
        $ingredient = new Ingredient();
        $ingredient->setNom('Farine');
        $ingredient->setQuantite('500g');
        $ingredient->setRecette($recette1);
        $manager->persist($ingredient);

        // Recette non publiée
        $recette2 = new Recette();
        $recette2->setTitre('Pasta Carbonara');
        $recette2->setDescription('Une pasta crémeuse avec des lardons et du parmesan râpé.');
        $recette2->setInstructions('Étape 1: Cuire les pâtes. Étape 2: Préparer la sauce.');
        $recette2->setTempsPreparation(20);
        $recette2->setTempsCuisson(15);
        $recette2->setDifficulte('moyen');
        $recette2->setNbPersonnes(2);
        $recette2->setPubliee(false);
        $recette2->setCategorie($categorie);
        $recette2->setAuteur($user);
        $manager->persist($recette2);

        $manager->flush();
    }
}