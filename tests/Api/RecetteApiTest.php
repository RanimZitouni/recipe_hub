<?php
namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RecetteApiTest extends WebTestCase
{
    // Test 1 — GET /api/recettes retourne 200
    public function testGetRecettesRetourne200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/recettes', [], [], [
            'HTTP_ACCEPT' => 'application/ld+json',
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
    }

    // Test 2 — POST /api/recettes avec données valides retourne 201
    public function testPostRecetteValide(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/recettes', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
            'HTTP_ACCEPT'  => 'application/ld+json',
        ], json_encode([
            'titre'            => 'Recette API Test',
            'description'      => 'Description test API avec suffisamment de caractères pour valider.',
            'instructions'     => 'Instructions test API.',
            'tempsPreparation' => 25,
            'nbPersonnes'      => 4,
            'difficulte'       => 'facile',
        ]));

        $this->assertResponseStatusCodeSame(201);
    }

    // Test 3 — POST /api/recettes avec titre vide retourne 422
    public function testPostRecetteTitreVideRetourne422(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/recettes', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
            'HTTP_ACCEPT'  => 'application/ld+json',
        ], json_encode([
            'titre'            => '',
            'description'      => 'Description test.',
            'instructions'     => 'Instructions test.',
            'tempsPreparation' => 25,
            'nbPersonnes'      => 4,
            'difficulte'       => 'facile',
        ]));

        $this->assertResponseStatusCodeSame(422);
    }
}