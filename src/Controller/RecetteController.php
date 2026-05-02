<?php
namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\Recette;
use App\Form\IngredientType;
use App\Form\RecetteType;
use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/recettes')]
class RecetteController extends AbstractController
{
    #[Route('', name: 'recette_liste', methods: ['GET'])]
    public function liste(RecetteRepository $repo): Response
    {
        return $this->render('recette/liste.html.twig', [
            'recettes' => $repo->findAll(),
        ]);
    }

    #[Route('/nouvelle', name: 'recette_nouvelle', methods: ['GET', 'POST'])]
    public function nouvelle(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $recette = new Recette();
        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('images_directory'), $newFilename);
                $recette->setImageName($newFilename);
            }

            $recette->setAuteur($this->getUser());
            $em->persist($recette);
            $em->flush();

            $this->addFlash('success', 'Recette créée avec succès !');
            return $this->redirectToRoute('recette_liste');
        }

        return $this->render('recette/form.html.twig', [
            'form' => $form,
            'titre' => 'Nouvelle recette',
        ]);
    }

    #[Route('/{id}', name: 'recette_detail', methods: ['GET'])]
    public function detail(Recette $recette): Response
    {
        return $this->render('recette/detail.html.twig', [
            'recette' => $recette,
        ]);
    }

    #[Route('/{id}/modifier', name: 'recette_modifier', methods: ['GET', 'POST'])]
    public function modifier(Request $request, Recette $recette, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('images_directory'), $newFilename);
                $recette->setImageName($newFilename);
            }

            $em->flush();
            $this->addFlash('success', 'Recette modifiée avec succès !');
            return $this->redirectToRoute('recette_liste');
        }

        return $this->render('recette/form.html.twig', [
            'form' => $form,
            'titre' => 'Modifier la recette',
            'recette' => $recette,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'recette_supprimer', methods: ['POST'])]
    public function supprimer(Request $request, Recette $recette, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('supprimer' . $recette->getId(), $request->request->get('_token'))) {
            $em->remove($recette);
            $em->flush();
            $this->addFlash('success', 'Recette supprimée avec succès !');
        }
        return $this->redirectToRoute('recette_liste');
    }

    #[Route('/{id}/ingredients/nouveau', name: 'ingredient_nouveau', methods: ['GET', 'POST'])]
    public function nouvelIngredient(Request $request, Recette $recette, EntityManagerInterface $em): Response
    {
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient->setRecette($recette);
            $em->persist($ingredient);
            $em->flush();
            $this->addFlash('success', 'Ingrédient ajouté !');
            return $this->redirectToRoute('recette_detail', ['id' => $recette->getId()]);
        }

        return $this->render('ingredient/form.html.twig', [
            'form' => $form,
            'recette' => $recette,
        ]);
    }
}