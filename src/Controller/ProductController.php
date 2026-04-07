<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('', name: 'app_product_index', methods: ['GET'])]
    public function index(Request $request, ProductRepository $repo): Response
    {
        $filters = [
            'name'      => $request->query->get('name'),
            'category'  => $request->query->get('category'),
            'country'   => $request->query->get('country'),
            'max_price' => $request->query->get('max_price'),
        ];

        $products = $repo->search($filters);
        $lowStock = $repo->findLowStock();

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'lowStock' => $lowStock,
            'filters'  => $filters,
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $product = new Product();
        $product->setCreatedAt(new \DateTime());
        $product->setUpdatedAt(new \DateTime());

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit créé avec succès !');
            return $this->redirectToRoute('app_product_index');
        }

        return $this->render('product/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        $product->setUpdatedAt(new \DateTime());

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Produit modifié avec succès !');
            return $this->redirectToRoute('app_product_index');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form'    => $form,
        ]);
    }

    #[Route('/{id}/duplicate', name: 'app_product_duplicate', methods: ['POST'])]
    public function duplicate(Product $product, EntityManagerInterface $em): Response
    {
        $copy = new Product();
        $copy->setName($product->getName() . ' (copie)');
        $copy->setDescription($product->getDescription());
        $copy->setPrice($product->getPrice());
        $copy->setOriginalPrice($product->getOriginalPrice());
        $copy->setStock($product->getStock());
        $copy->setStockThreshold($product->getStockThreshold());
        $copy->setCountry($product->getCountry());
        $copy->setCategory($product->getCategory());
        $copy->setExpirationDate($product->getExpirationDate());
        $copy->setPromoTag($product->getPromoTag());
        $copy->setCreatedAt(new \DateTime());
        $copy->setUpdatedAt(new \DateTime());

        $em->persist($copy);
        $em->flush();

        $this->addFlash('success', 'Produit dupliqué.');
        return $this->redirectToRoute('app_product_edit', ['id' => $copy->getId()]);
    }

    #[Route('/{id}/delete', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $em->remove($product);
            $em->flush();
            $this->addFlash('success', 'Produit supprimé.');
        }

        return $this->redirectToRoute('app_product_index');
    }
}