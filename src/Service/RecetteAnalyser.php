<?php
namespace App\Service;

use App\Entity\Recette;
use App\Repository\RecetteRepository;

class RecetteAnalyser
{
    public function __construct(private RecetteRepository $repo) {}

    public function getTempsTotal(Recette $r): int
    {
        return $r->getTempsPreparation() + ($r->getTempsCuisson() ?? 0);
    }

    public function getTotalRecettesPubliees(): int
    {
        return count($this->repo->findBy(['publiee' => true]));
    }

    public function getRecettesParCategorie(): array
    {
        $recettes = $this->repo->findAll();
        $result = [];
        foreach ($recettes as $recette) {
            $categorie = $recette->getCategorie();
            $nom = $categorie ? $categorie->getNom() : 'Sans catégorie';
            if (!isset($result[$nom])) {
                $result[$nom] = 0;
            }
            $result[$nom]++;
        }
        return $result;
    }

    public function getMoyenneIngredients(): float
    {
        $recettes = $this->repo->findAll();
        if (count($recettes) === 0) return 0.0;
        $total = 0;
        foreach ($recettes as $recette) {
            $total += count($recette->getIngredients());
        }
        return round($total / count($recettes), 1);
    }
}
