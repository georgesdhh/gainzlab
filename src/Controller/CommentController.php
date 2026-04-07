<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\ProductRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/comment')]
class CommentController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/add/{id}', name: 'app_comment_add', methods: ['POST'])]
    public function add(int $id, Request $request, ProductRepository $productRepo, EntityManagerInterface $em): Response
    {
        $product = $productRepo->find($id);

        if (!$product) {
            throw $this->createNotFoundException();
        }

        $content = trim($request->request->get('content', ''));

        if ($content !== '') {
            $comment = new Comment();
            $comment->setContent($content);
            $comment->setAuthor($this->getUser());
            $comment->setProduct($product);
            $comment->setCreatedAt(new \DateTime());
            $em->persist($comment);
            $em->flush();
            $this->addFlash('success', 'Commentaire publié.');
        }

        return $this->redirectToRoute('app_product_show', ['id' => $id]);
    }

    #[Route('/delete/{id}', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(Comment $comment, Request $request, EntityManagerInterface $em): Response
    {
        $productId = $comment->getProduct()->getId();

        $isOwner = $this->getUser() === $comment->getAuthor();
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        if (!$isOwner && !$isAdmin) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete-comment-' . $comment->getId(), $request->request->get('_token'))) {
            $em->remove($comment);
            $em->flush();
            $this->addFlash('success', 'Commentaire supprimé.');
        }

        return $this->redirectToRoute('app_product_show', ['id' => $productId]);
    }
}