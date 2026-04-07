<?php

namespace App\Controller;

use App\Entity\Rating;
use App\Repository\ProductRepository;
use App\Repository\RatingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/rating')]
class RatingController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/add/{id}', name: 'app_rating_add', methods: ['POST'])]
    public function add(int $id, Request $request, ProductRepository $productRepo, RatingRepository $ratingRepo, EntityManagerInterface $em): Response
    {
        $product = $productRepo->find($id);

        if (!$product) {
            throw $this->createNotFoundException();
        }

        $score = (int) $request->request->get('score');

        if ($score < 1 || $score > 5) {
            $this->addFlash('error', 'Note invalide.');
            return $this->redirectToRoute('app_product_show', ['id' => $id]);
        }

        // Vérifie si l'utilisateur a déjà noté ce produit
        $existing = $ratingRepo->findOneBy(['author' => $this->getUser(), 'product' => $product]);

        if ($existing) {
            $existing->setScore($score);
            $this->addFlash('success', 'Note mise à jour.');
        } else {
            $rating = new Rating();
            $rating->setScore($score);
            $rating->setAuthor($this->getUser());
            $rating->setProduct($product);
            $em->persist($rating);
            $this->addFlash('success', 'Note publiée.');
        }

        $em->flush();

        return $this->redirectToRoute('app_product_show', ['id' => $id]);
    }
}
