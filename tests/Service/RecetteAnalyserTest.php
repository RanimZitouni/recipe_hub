<?php
namespace App\Tests\Service;

use App\Entity\Recette;
use App\Entity\Ingredient;
use App\Repository\RecetteRepository;
use App\Service\RecetteAnalyser;
use PHPUnit\Framework\TestCase;

class RecetteAnalyserTest extends TestCase
{
    private RecetteAnalyser $analyser;
    private $repoMock;

    protected function setUp(): void
    {
        $this->repoMock = $this->createMock(RecetteRepository::class);
        $this->analyser = new RecetteAnalyser($this->repoMock);
    }

    // Test 1 — getTempsTotal avec cuisson
    public function testGetTempsTotalAvecCuisson(): void
    {
        $recette = new Recette();
        $recette->setTempsPreparation(30);
        $recette->setTempsCuisson(20);

        $result = $this->analyser->getTempsTotal($recette);

        $this->assertEquals(50, $result);
    }

    // Test 2 — getTempsTotal sans cuisson (null)
    public function testGetTempsTotalSansCuisson(): void
    {
        $recette = new Recette();
        $recette->setTempsPreparation(30);
        $recette->setTempsCuisson(null);

        $result = $this->analyser->getTempsTotal($recette);

        $this->assertEquals(30, $result);
    }

    // Test 3 — getTotalRecettesPubliees
    public function testGetTotalRecettesPubliees(): void
    {
        $recette1 = new Recette();
        $recette1->setPubliee(true);

        $recette2 = new Recette();
        $recette2->setPubliee(false);

        $this->repoMock
            ->expects($this->once())
            ->method('findBy')
            ->with(['publiee' => true])
            ->willReturn([$recette1]);

        $result = $this->analyser->getTotalRecettesPubliees();

        $this->assertEquals(1, $result);
    }

    // Test 4 — getMoyenneIngredients retourne 0 si aucune recette
    public function testGetMoyenneIngredientsAucuneRecette(): void
    {
        $this->repoMock
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $result = $this->analyser->getMoyenneIngredients();

        $this->assertEquals(0.0, $result);
    }
}
