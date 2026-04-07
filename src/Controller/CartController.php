<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Product;
use App\Service\CartService;

#[Route('/cart')]
class CartController extends AbstractController
{
    // Liste tous les paniers
    #[Route('', name: 'app_cart_index', methods: ['GET'])]
    public function index(CartRepository $repo): Response
    {
        return $this->render('cart/index.html.twig', [
            'carts' => $repo->findBy(['user' => $this->getUser()], ['createdAt' => 'ASC']),
        ]);
    }

    // Créer un nouveau panier
    #[Route('/new', name: 'app_cart_new', methods: ['POST'])]
    public function new(EntityManagerInterface $em): Response
    {
        $cart = new Cart();
        $cart->setUser($this->getUser());
        $cart->setCreatedAt(new \DateTime());
        $cart->setUpdatedAt(new \DateTime());
        $em->persist($cart);
        $em->flush();

        $this->addFlash('success', 'Panier créé !');
        return $this->redirectToRoute('app_cart_show', ['id' => $cart->getId()]);
    }

    // Détail d'un panier
    #[Route('/{id}', name: 'app_cart_show', methods: ['GET'])]
    public function show(Cart $cart, CartRepository $repo): Response
    {
        $userCarts = $repo->findBy(['user' => $this->getUser()], ['createdAt' => 'ASC']);
        $cartNumber = array_search($cart, $userCarts) + 1;

        return $this->render('cart/show.html.twig', [
            'cart'       => $cart,
            'cartNumber' => $cartNumber,
        ]);
    }

    // Ajouter un produit au panier
    #[Route('/{id}/add', name: 'app_cart_add', methods: ['GET', 'POST'])]
    public function addProduct(Request $request, Cart $cart, ProductRepository $productRepo, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $productId = $request->request->get('product_id');
            $quantity  = (int) $request->request->get('quantity', 1);
            $product   = $productRepo->find($productId);

            if ($product) {
                // Vérifie si le produit est déjà dans le panier
                $existingItem = null;
                foreach ($cart->getCartItems() as $item) {
                    if ($item->getProduct()->getId() === $product->getId()) {
                        $existingItem = $item;
                        break;
                    }
                }

                $quantiteActuelle = $existingItem ? $existingItem->getQuantity() : 0;
                $quantiteTotale   = $quantiteActuelle + $quantity;

                if ($quantiteTotale > $product->getStock()) {
                    $this->addFlash('error', 'Stock insuffisant pour ' . $product->getName());
                } else {
                    if ($existingItem) {
                        $existingItem->setQuantity($quantiteTotale);
                    } else {
                        $cartItem = new CartItem();
                        $cartItem->setCart($cart);
                        $cartItem->setProduct($product);
                        $cartItem->setQuantity($quantity);
                        $em->persist($cartItem);
                    }
                    $cart->setUpdatedAt(new \DateTime());
                    $em->flush();
                    $this->addFlash('success', 'Produit ajouté !');
                }
            }

            return $this->redirectToRoute('app_cart_show', ['id' => $cart->getId()]);
        }

        return $this->render('cart/add.html.twig', [
            'cart'     => $cart,
            'products' => $productRepo->findAll(),
        ]);
    }

    // Supprimer un article du panier
    #[Route('/{cartId}/item/{productId}/delete', name: 'app_cart_remove_item', methods: ['POST'])]
    public function removeItem(int $cartId, int $productId, CartRepository $cartRepo, ProductRepository $productRepo, EntityManagerInterface $em): Response
    {
        $cart    = $cartRepo->find($cartId);
        $product = $productRepo->find($productId);

        foreach ($cart->getCartItems() as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                $em->remove($item);
                break;
            }
        }

        $em->flush();
        $this->addFlash('success', 'Article supprimé.');
        return $this->redirectToRoute('app_cart_show', ['id' => $cartId]);
    }

    // Supprimer un panier entier
    #[Route('/{id}/delete', name: 'app_cart_delete', methods: ['POST'])]
    public function delete(Request $request, Cart $cart, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cart->getId(), $request->request->get('_token'))) {
            $em->remove($cart);
            $em->flush();
            $this->addFlash('success', 'Panier supprimé.');
        }

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/{id}/validate', name: 'app_cart_validate', methods: ['POST'])]
    public function validate(Cart $cart, EntityManagerInterface $em): Response
    {
        foreach ($cart->getCartItems() as $item) {
            $product = $item->getProduct();
            $newStock = $product->getStock() - $item->getQuantity();

            if ($newStock < 0) {
                $this->addFlash('error', 'Stock insuffisant pour ' . $product->getName() . '. Commande annulée.');
                return $this->redirectToRoute('app_cart_show', ['id' => $cart->getId()]);
            }

            $product->setStock($newStock);
        }

        $cart->setStatus('validated');
        $cart->setUpdatedAt(new \DateTime());
        $em->flush();

        $this->addFlash('success', 'Commande validée ! Merci pour votre achat.');
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/quick-add/{id}', name: 'app_cart_quick_add', methods: ['POST'])]
    public function quickAdd(Product $product, CartService $cartService): Response
    {
        $cart = $cartService->getOrCreateRecentCart();
        try {
            $cartService->addItem($cart, $product, 1);
            $this->addFlash('success', $product->getName() . ' ajouté au panier !');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirectToRoute('app_cart_show', ['id' => $cart->getId()]);
    }

    #[Route('/{cartId}/item/{productId}/quantity', name: 'app_cart_update_quantity', methods: ['POST'])]
    public function updateQuantity(int $cartId, int $productId, Request $request, CartRepository $cartRepo, ProductRepository $productRepo, EntityManagerInterface $em):
    Response
    {
        $cart     = $cartRepo->find($cartId);
        $product  = $productRepo->find($productId);
        $quantity = (int) $request->request->get('quantity', 1);

        foreach ($cart->getCartItems() as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                if ($quantity <= 0) {
                    $em->remove($item);
                } elseif ($quantity > $product->getStock()) {
                    $this->addFlash('error', 'Stock insuffisant.');
                    return $this->redirectToRoute('app_cart_show', ['id' => $cartId]);
                } else {
                    $item->setQuantity($quantity);
                }
                break;
            }
        }

        $em->flush();
        $this->addFlash('success', 'Quantité mise à jour.');
        return $this->redirectToRoute('app_cart_show', ['id' => $cartId]);
    }



}