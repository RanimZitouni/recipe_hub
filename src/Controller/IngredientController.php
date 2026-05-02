<?php
namespace App\Controller;

use App\Entity\Ingredient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IngredientController extends AbstractController
{
    #[Route('/ingredients/{id}/supprimer', name: 'ingredient_supprimer', methods: ['POST'])]
    public function supprimer(Request $request, Ingredient $ingredient, EntityManagerInterface $em): Response
    {
        $recetteId = $ingredient->getRecette()->getId();
        if ($this->isCsrfTokenValid('supprimer' . $ingredient->getId(), $request->request->get('_token'))) {
            $em->remove($ingredient);
            $em->flush();
            $this->addFlash('success', 'Ingrédient supprimé !');
        }
        return $this->redirectToRoute('recette_detail', ['id' => $recetteId]);
    }
}