<?php
namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\Recette;
use App\Entity\User;
use App\Form\IngredientType;
use App\Form\RecetteFilterType;
use App\Form\RecetteType;
use App\Repository\RecetteRepository;
use App\Service\FileUploader;
use App\Service\RecetteAnalyser;
use App\Service\RecetteMailer;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/recettes')]
class RecetteController extends AbstractController
{
    public function __construct(
        private RecetteAnalyser $analyser,
        private RecetteMailer $recetteMailer,
        private FileUploader $fileUploader,
        private EventDispatcherInterface $dispatcher
    ) {}

    #[Route('', name: 'recette_liste', methods: ['GET'])]
public function liste(RecetteRepository $repo, Request $request, PaginatorInterface $paginator): Response
{
    $filterForm = $this->createForm(RecetteFilterType::class, null, [
        'method' => 'GET',
        'csrf_protection' => false,
    ]);
    $filterForm->handleRequest($request);

    // Build a query builder so paginator can sort and paginate
    $qb = $repo->createQueryBuilder('r');

    if ($filterForm->isSubmitted() && $filterForm->isValid()) {
        $data = $filterForm->getData();
        if (!empty($data['titre'])) {
            $qb->andWhere('r.titre LIKE :titre')->setParameter('titre', '%' . $data['titre'] . '%');
        }
        if (!empty($data['categorie'])) {
            $qb->andWhere('r.categorie = :cat')->setParameter('cat', $data['categorie']);
        }
        if (!empty($data['difficulte'])) {
            $qb->andWhere('r.difficulte = :diff')->setParameter('diff', $data['difficulte']);
        }
        if (!empty($data['tag'])) {
            $qb->innerJoin('r.tags', 't')->andWhere('t = :tag')->setParameter('tag', $data['tag']);
        }
    }

    $qb->orderBy('r.dateCreation', 'DESC');

    $page = $request->query->getInt('page', 1);
    $pagination = $paginator->paginate(
        $qb,
        $page,
        9,
        ['defaultSortFieldName' => 'r.titre', 'defaultSortDirection' => 'ASC']
    );

    return $this->render('recette/liste.html.twig', [
        'recettes'             => $pagination,
        'pagination'           => $pagination,
        'filterForm'           => $filterForm->createView(),
        'totalPubliees'        => $this->analyser->getTotalRecettesPubliees(),
        'recettesParCategorie' => $this->analyser->getRecettesParCategorie(),
        'moyenneIngredients'   => $this->analyser->getMoyenneIngredients(),
    ]);
}

    #[Route('/nouvelle', name: 'recette_nouvelle', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_CUISINIER')]
   #[Route('/nouvelle', name: 'recette_nouvelle', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_CUISINIER')]
public function nouvelle(Request $request, EntityManagerInterface $em): Response
{
    $recette = new Recette();
    $form = $this->createForm(RecetteType::class, $recette);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $imageFile = $form->get('imageFile')->getData();
        if ($imageFile) {
            $fileName = $this->fileUploader->upload($imageFile);
            $recette->setImageName($fileName);
        }

        $recette->setAuteur($this->getUser());

        $em->persist($recette);
        $em->flush();
        $event = new \App\Event\RecipeCreatedEvent($recette);
        $this->dispatcher->dispatch($event);
        if ($recette->isPubliee()) {
            try {
                $this->recetteMailer->sendNouvelleRecetteEmail(
                    $recette,
                    'admin@recipehub.com'
                );
            } catch (\Exception $e) {}
        }

        $this->addFlash('success', 'Recette créée avec succès !');
        return $this->redirectToRoute('recette_liste');
    }

    return $this->render('recette/form.html.twig', [
        'form'  => $form,
        'titre' => 'Nouvelle recette',
    ]);
}

    #[Route('/{id}', name: 'recette_detail', methods: ['GET'])]
public function detail(Recette $recette, RequestStack $requestStack): Response
{
    $session = $requestStack->getSession();
    $favoris = $session->get('favoris', []);
    $isFavori = in_array($recette->getId(), $favoris);

    return $this->render('recette/detail.html.twig', [
        'recette'  => $recette,
        'isFavori' => $isFavori,
    ]);
}

    #[Route('/{id}/modifier', name: 'recette_modifier', methods: ['GET', 'POST'])]
    public function modifier(Request $request, Recette $recette, EntityManagerInterface $em): Response
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
                // Supprime l'ancienne image
                if ($recette->getImageName()) {
                    $this->fileUploader->remove($recette->getImageName());
                }
                // Upload la nouvelle
                $fileName = $this->fileUploader->upload($imageFile);
                $recette->setImageName($fileName);
            }

            $em->flush();
            $this->addFlash('success', 'Recette modifiée avec succès !');
            return $this->redirectToRoute('recette_liste');
        }

        return $this->render('recette/form.html.twig', [
            'form'    => $form,
            'titre'   => 'Modifier la recette',
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
            // Supprime le fichier image physique
            if ($recette->getImageName()) {
                $this->fileUploader->remove($recette->getImageName());
            }
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
            'form'    => $form,
            'recette' => $recette,
        ]);
    }
}