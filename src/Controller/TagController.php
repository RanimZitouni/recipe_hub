<?php
namespace App\Controller;

use App\Entity\TagRecette;
use App\Form\TagRecetteType;
use App\Repository\TagRecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/tags')]
class TagController extends AbstractController
{
    #[Route('', name: 'tag_liste', methods: ['GET'])]
    public function liste(TagRecetteRepository $repo): Response
    {
        return $this->render('tag/liste.html.twig', [
            'tags' => $repo->findAll(),
        ]);
    }

    #[Route('/nouveau', name: 'tag_nouveau', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function nouveau(Request $request, EntityManagerInterface $em): Response
    {
        $tag = new TagRecette();
        $form = $this->createForm(TagRecetteType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($tag);
            $em->flush();
            $this->addFlash('success', 'Tag créé !');
            return $this->redirectToRoute('tag_liste');
        }

        return $this->render('tag/form.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'tag_supprimer', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function supprimer(Request $request, TagRecette $tag, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('supprimer' . $tag->getId(), $request->request->get('_token'))) {
            $em->remove($tag);
            $em->flush();
            $this->addFlash('success', 'Tag supprimé !');
        }
        return $this->redirectToRoute('tag_liste');
    }
}