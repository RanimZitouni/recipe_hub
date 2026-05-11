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
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
    #[IsGranted('ROLE_ADMIN')]
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
    #[Route('/{id}/modifier', name: 'categorie_modifier', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_ADMIN')]
public function modifier(Request $request, CategorieRecette $categorie, EntityManagerInterface $em): Response
{
    $form = $this->createForm(CategorieRecetteType::class, $categorie);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();

        $this->addFlash('success', 'Catégorie modifiée !');

        return $this->redirectToRoute('categorie_liste');
    }

    return $this->render('categorie/form.html.twig', [
        'form' => $form,
    ]);
}

    #[Route('/{id}/supprimer', name: 'categorie_supprimer', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function supprimer(Request $request, CategorieRecette $categorie, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('supprimer_categorie_' . $categorie->getId(), $request->request->get('_token'))) {
            $em->remove($categorie);
            $em->flush();
            $this->addFlash('success', 'Catégorie supprimée !');
        }

        return $this->redirectToRoute('categorie_liste');
}
}