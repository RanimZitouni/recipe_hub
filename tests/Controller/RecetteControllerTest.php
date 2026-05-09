<?php
namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RecetteControllerTest extends WebTestCase
{
    // Test 1 — Page liste retourne 200
    public function testListeRetourne200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/recettes');

        $this->assertResponseStatusCodeSame(200);
    }

    // Test 2 — Page liste contient des cards
    public function testListeContientCards(): void
    {
        $client = static::createClient();
        $client->request('GET', '/recettes');

        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorExists('.recipe-card');
    }

    // Test 3 — Page nouvelle sans auth retourne 302
    public function testNouvelleRedirectSansAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/recettes/nouvelle');

        $this->assertResponseStatusCodeSame(302);
    }

    // Test 4 — Création recette avec auth
    public function testCreationRecetteAvecAuth(): void
    {
        $client = static::createClient();

        // Récupère l'utilisateur de test
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'test@recipehub.com']);

        $client->loginUser($user);
        $client->request('GET', '/recettes/nouvelle');

        $this->assertResponseStatusCodeSame(200);

        $client->submitForm('Enregistrer', [
    'recette[titre]'            => 'Recette Test PHPUnit',
    'recette[description]'      => 'Description de test pour PHPUnit avec au moins 30 caractères.',
    'recette[instructions]'     => 'Instructions de test pour PHPUnit.',
    'recette[tempsPreparation]' => 20,
    'recette[nbPersonnes]'      => 4,
    'recette[difficulte]'       => 'facile',
]);

        $this->assertResponseRedirects('/recettes');
    }

    // Test 5 — Message flash après création
    public function testFlashApresCreation(): void
    {
        $client = static::createClient();

        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'test@recipehub.com']);

        $client->loginUser($user);
        $client->request('GET', '/recettes/nouvelle');

        $client->submitForm('Enregistrer', [
            'recette[titre]'            => 'Recette Flash Test',
            'recette[description]'      => 'Description de test pour PHPUnit avec au moins 30 caractères.',
            'recette[instructions]'     => 'Instructions de test.',
            'recette[tempsPreparation]' => 15,
            'recette[nbPersonnes]'      => 2,
            'recette[difficulte]'       => 'facile',
        ]);

        $client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }
}
