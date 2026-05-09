<?php
namespace App\Controller;

use App\Repository\RecetteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FavorisController extends AbstractController
{
    public function __construct(private RequestStack $requestStack) {}

    #[Route('/mes-favoris', name: 'recette_favoris', methods: ['GET'])]
    public function index(RecetteRepository $repo): Response
    {
        $session = $this->requestStack->getSession();
        $favorisIds = $session->get('favoris', []);
        $recettes = [];

        foreach ($favorisIds as $id) {
            $recette = $repo->find($id);
            if ($recette) {
                $recettes[] = $recette;
            }
        }

        return $this->render('favoris/index.html.twig', [
            'recettes' => $recettes,
        ]);
    }

    #[Route('/favoris/add/{id}', name: 'app_favori_add', methods: ['POST'])]
    public function add(int $id): Response
    {
        $session = $this->requestStack->getSession();
        $favoris = $session->get('favoris', []);

        if (!in_array($id, $favoris)) {
            $favoris[] = $id;
            $session->set('favoris', $favoris);
            $this->addFlash('success', 'Recette ajoutée aux favoris !');
        }

        return $this->redirectToRoute('recette_detail', ['id' => $id]);
    }

    #[Route('/favoris/remove/{id}', name: 'app_favori_remove', methods: ['POST'])]
    public function remove(int $id): Response
    {
        $session = $this->requestStack->getSession();
        $favoris = $session->get('favoris', []);
        $favoris = array_filter($favoris, fn($f) => $f !== $id);
        $session->set('favoris', array_values($favoris));

        $this->addFlash('success', 'Recette retirée des favoris !');
        return $this->redirectToRoute('recette_favoris');
    }
}