<?php
namespace App\Controller;
use App\Entity\Ingredient;
use App\Entity\Recette;
use App\Entity\User;
use App\Form\IngredientType;
use App\Form\RecetteType;
use App\Repository\RecetteRepository;
use App\Service\RecetteAnalyser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Service\RecetteMailer;

#[Route('/recettes')]
class RecetteController extends AbstractController
{
    public function __construct(private RecetteAnalyser $analyser , private RecetteMailer $recetteMailer) {}
    
    #[Route('', name: 'recette_liste', methods: ['GET'])]
    public function liste(RecetteRepository $repo, Request $request): Response
    {
        $form = $this->createForm(\App\Form\RecetteFilterType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);

        $data = $form->getData() ?? [];
        $titre = $data['titre'] ?? null;
        $categorie = $data['categorie'] ?? null;
        $difficulte = $data['difficulte'] ?? null;
        $tag = $data['tag'] ?? null;

        $recettes = $repo->findByFilters($titre, $categorie, $difficulte, $tag);

        return $this->render('recette/liste.html.twig', [
            'recettes'             => $recettes,
            'totalPubliees'        => $this->analyser->getTotalRecettesPubliees(),
            'recettesParCategorie' => $this->analyser->getRecettesParCategorie(),
            'moyenneIngredients'   => $this->analyser->getMoyenneIngredients(),
            'filterForm'           => $form->createView(),
        ]);
    }
#[Route('/nouvelle', name: 'recette_nouvelle', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_CUISINIER')]
public function nouvelle(
    Request $request,
    EntityManagerInterface $em,
    SluggerInterface $slugger
): Response {
    $recette = new Recette();
    $form = $this->createForm(RecetteType::class, $recette);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $imageFile = $form->get('imageFile')->getData();

        if ($imageFile) {
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move($this->getParameter('images_directory'), $newFilename);
            $recette->setImageName($newFilename);
        }

        $recette->setAuteur($this->getUser());
        $em->persist($recette);
        $em->flush();
        try {
            $this->recetteMailer->sendNouvelleRecetteEmail(
                $recette,
                'admin@recipehub.com'
            );
        } catch (\Exception $e) {
            dd($e->getMessage()); 
        }

        $this->addFlash('success', 'Recette créée avec succès !');
        return $this->redirectToRoute('recette_liste');
    }
    
    return $this->render('recette/form.html.twig', [
        'form' => $form,
        'titre' => 'Nouvelle recette',
    ]);
}

    #[Route('/{id}', name: 'recette_detail', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function detail(Recette $recette, RequestStack $requestStack): Response
    {
        return $this->render('recette/detail.html.twig', [
            'recette' => $recette,
            'isFavori' => $this->isFavori($recette, $requestStack),
        ]);
    }

    #[Route('/{id}/modifier', name: 'recette_modifier', methods: ['GET', 'POST'])]
    public function modifier(Request $request, Recette $recette, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || (!$this->isGranted('ROLE_ADMIN') && $recette->getAuteur()?->getId() !== $currentUser->getId())) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette recette.');
        }

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
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || (!$this->isGranted('ROLE_ADMIN') && $recette->getAuteur()?->getId() !== $currentUser->getId())) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer cette recette.');
        }

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
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || (!$this->isGranted('ROLE_ADMIN') && $recette->getAuteur()?->getId() !== $currentUser->getId())) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette recette.');
        }

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
 
#[Route('/favoris/{id}', name: 'app_favori_add', methods: ['POST'])]
public function addFavori(
    Recette $recette,
    Request $request,
    RequestStack $requestStack
): Response {

    if (!$this->isCsrfTokenValid('add_favori' . $recette->getId(), $request->request->get('_token'))) {
        throw $this->createAccessDeniedException('Token CSRF invalide.');
    }

    $session = $requestStack->getSession();

    $favoris = $session->get('favoris', []);

    if (!in_array($recette->getId(), $favoris)) {
        $favoris[] = $recette->getId();
    }

    $session->set('favoris', $favoris);

    return $this->redirectToRoute('recette_detail', [
        'id' => $recette->getId()
    ]);
}

#[Route('/favoris/{id}/supprimer', name: 'app_favori_remove', methods: ['POST'])]
public function removeFavori(
    Recette $recette,
    Request $request,
    RequestStack $requestStack
): Response {

    if (!$this->isCsrfTokenValid('remove_favori' . $recette->getId(), $request->request->get('_token'))) {
        throw $this->createAccessDeniedException('Token CSRF invalide.');
    }

    $session = $requestStack->getSession();

    $favoris = $session->get('favoris', []);

    if (($key = array_search($recette->getId(), $favoris)) !== false) {
        unset($favoris[$key]);
    }

    $session->set('favoris', array_values($favoris));

    return $this->redirectToRoute('recette_detail', [
        'id' => $recette->getId()
    ]);
}

#[Route('/mes-favoris', name: 'recette_favoris', methods: ['GET'])]
public function favoris(RecetteRepository $repo, RequestStack $requestStack): Response
{
    $session = $requestStack->getSession();
    $favorisIds = $session->get('favoris', []);
    
    $recettes = [];
    if (!empty($favorisIds)) {
        // Get recipes and preserve the order from favorites
        $recettesEntities = $repo->findBy(['id' => $favorisIds]);
        
        // Sort recipes to match the order in favorites
        $recettesMap = [];
        foreach ($recettesEntities as $recette) {
            $recettesMap[$recette->getId()] = $recette;
        }
        
        foreach ($favorisIds as $id) {
            if (isset($recettesMap[$id])) {
                $recettes[] = $recettesMap[$id];
            }
        }
    }

    return $this->render('recette/favoris.html.twig', [
        'recettes' => $recettes,
    ]);
}

private function isFavori(Recette $recette, RequestStack $requestStack): bool
{
    $session = $requestStack->getSession();
    $favoris = $session->get('favoris', []);
    return in_array($recette->getId(), $favoris);
}
}