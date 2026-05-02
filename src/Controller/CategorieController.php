<?php
namespace App\Controller;

use App\Entity\CategorieRecette;
use App\Form\CategorieRecetteType;
use App\Repository\CategorieRecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/categories')]
class CategorieController extends AbstractController
{
    #[Route('', name: 'categorie_liste', methods: ['GET'])]
    public function liste(CategorieRecetteRepository $repo): Response
    {
        return $this->render('categorie/liste.html.twig', [
            'categories' => $repo->findAll(),
        ]);
    }

    #[Route('/nouvelle', name: 'categorie_nouvelle', methods: ['GET', 'POST'])]
    public function nouvelle(Request $request, EntityManagerInterface $em): Response
    {
        $categorie = new CategorieRecette();
        $form = $this->createForm(CategorieRecetteType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($categorie);
            $em->flush();
            $this->addFlash('success', 'Catégorie créée !');
            return $this->redirectToRoute('categorie_liste');
        }

        return $this->render('categorie/form.html.twig', [
            'form' => $form,
        ]);
    }
}