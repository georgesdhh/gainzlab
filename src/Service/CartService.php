<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class CartService
{
    public function __construct(
        private CartRepository $cartRepository,
        private EntityManagerInterface $em,
        private Security $security
    ) {}


    public function getOrCreateRecentCart(): Cart
    {
        $cart = $this->cartRepository->findRecentCart();

        if (!$cart) {
            $cart = new Cart();
            $cart->setCreatedAt(new \DateTime());
            $cart->setUpdatedAt(new \DateTime());
            $cart->setUser($this->security->getUser());
            $this->em->persist($cart);
            $this->em->flush();
        }

        return $cart;
    }

    public function getRecentCart(): ?Cart
    {
        return $this->cartRepository->findRecentCart();
    }

    public function addItem(Cart $cart, Product $product, int $qty): void
    {
        $existingItem = null;
        foreach ($cart->getCartItems() as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                $existingItem = $item;
                break;
            }
        }

        $quantiteActuelle = $existingItem ? $existingItem->getQuantity() : 0;
        $quantiteTotale   = $quantiteActuelle + $qty;

        if ($quantiteTotale > $product->getStock()) {
            throw new \Exception(
                "Stock insuffisant pour {$product->getName()}. "
                . "Disponible : {$product->getStock()}, demandé : {$quantiteTotale}."
            );
        }

        if ($existingItem) {
            $existingItem->setQuantity($quantiteTotale);
        } else {
            $cartItem = new CartItem();
            $cartItem->setCart($cart);
            $cartItem->setProduct($product);
            $cartItem->setQuantity($qty);
            $this->em->persist($cartItem);
        }

        $cart->setUpdatedAt(new \DateTime());
        $this->em->flush();
    }

    public function removeItem(Cart $cart, Product $product): void
    {
        foreach ($cart->getCartItems() as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                $this->em->remove($item);
                break;
            }
        }
        $this->em->flush();
    }


}

