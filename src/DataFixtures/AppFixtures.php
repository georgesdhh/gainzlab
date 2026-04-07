<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Rating;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;



class AppFixtures extends Fixture
{

    public function __construct(private UserPasswordHasherInterface $hasher) {}


    public function load(ObjectManager $manager): void
    {
        // Admin — créé une seule fois
        $admin = new User();
        $admin->setFirstName('Admin');
        $admin->setLastName('GainzLab');
        $admin->setEmail('admin@gainzlab.fr');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // Utilisateurs de test
        $user1 = new User();
        $user1->setFirstName('Lucas');
        $user1->setLastName('Martin');
        $user1->setEmail('lucas@test.fr');
        $user1->setRoles(['ROLE_USER']);
        $user1->setPassword($this->hasher->hashPassword($user1, 'user123'));
        $manager->persist($user1);

        $user2 = new User();
        $user2->setFirstName('Camille');
        $user2->setLastName('Dupont');
        $user2->setEmail('camille@test.fr');
        $user2->setRoles(['ROLE_USER']);
        $user2->setPassword($this->hasher->hashPassword($user2, 'user123'));
        $manager->persist($user2);

        $produits = [
            ['name' => 'Whey Protein Vanille',    'category' => 'Protéines',     'price' => 34.99, 'originalPrice' => null,  'stock' => 50, 'country' => 'France',    'promoTag' => 'Bestseller',      'threshold' => 10],
            ['name' => 'Whey Protein Chocolat',   'category' => 'Protéines',     'price' => 34.99, 'originalPrice' => null,  'stock' => 3,  'country' => 'France',    'promoTag' => null,              'threshold' => 10],
            ['name' => 'Caséine Nocturne',        'category' => 'Protéines',     'price' => 42.90, 'originalPrice' => null,  'stock' => 20, 'country' => 'Allemagne', 'promoTag' => null,              'threshold' => 5],
            ['name' => 'Créatine Monohydrate',    'category' => 'Créatine',      'price' => 14.99, 'originalPrice' => 19.99, 'stock' => 80, 'country' => 'USA',       'promoTag' => 'Promo',           'threshold' => 15],
            ['name' => 'Créatine HCL',            'category' => 'Créatine',      'price' => 27.50, 'originalPrice' => null,  'stock' => 4,  'country' => 'USA',       'promoTag' => null,              'threshold' => 10],
            ['name' => 'BCAA 2:1:1',              'category' => 'Acides Aminés', 'price' => 24.99, 'originalPrice' => null,  'stock' => 60, 'country' => 'UK',        'promoTag' => null,              'threshold' => 10],
            ['name' => 'Glutamine Pure',          'category' => 'Acides Aminés', 'price' => 18.90, 'originalPrice' => null,  'stock' => 2,  'country' => 'Espagne',   'promoTag' => null,              'threshold' => 8],
            ['name' => 'Vitamine D3',             'category' => 'Vitamines',     'price' => 9.99,  'originalPrice' => null,  'stock' => 100,'country' => 'France',    'promoTag' => 'Nouveau',         'threshold' => 20],
            ['name' => 'Oméga 3',                 'category' => 'Vitamines',     'price' => 14.90, 'originalPrice' => null,  'stock' => 45, 'country' => 'Norvège',   'promoTag' => null,              'threshold' => 10],
            ['name' => 'Brûleur Thermogénique',   'category' => 'Brûleurs',      'price' => 39.99, 'originalPrice' => null,  'stock' => 15, 'country' => 'USA',       'promoTag' => 'Édition Limitée', 'threshold' => 5],
            ['name' => 'L-Carnitine Liquide',     'category' => 'Brûleurs',      'price' => 17.50, 'originalPrice' => 22.50, 'stock' => 8,  'country' => 'Italie',    'promoTag' => 'Promo',           'threshold' => 5],
            ['name' => 'Barre Protéinée Noisette','category' => 'Barres & Snacks','price' => 2.99, 'originalPrice' => null,  'stock' => 200,'country' => 'Belgique',  'promoTag' => null,              'threshold' => 30],
            ['name' => 'Barre Protéinée Cookies', 'category' => 'Barres & Snacks','price' => 2.99, 'originalPrice' => null,  'stock' => 5,  'country' => 'Belgique',  'promoTag' => 'Bestseller',      'threshold' => 30],
            ['name' => 'Électrolytes Citron',     'category' => 'Hydratation',   'price' => 12.99, 'originalPrice' => null,  'stock' => 70, 'country' => 'France',    'promoTag' => 'Nouveau',         'threshold' => 15],
            ['name' => 'Boisson Isotonique',      'category' => 'Hydratation',   'price' => 16.90, 'originalPrice' => null,  'stock' => 0,  'country' => 'Allemagne', 'promoTag' => null,              'threshold' => 10],
        ];

        $products = [];
        foreach ($produits as $data) {
            $product = new Product();
            $product->setName($data['name']);
            $product->setCategory($data['category']);
            $product->setPrice($data['price']);
            $product->setStock($data['stock']);
            $product->setCountry($data['country']);
            $product->setPromoTag($data['promoTag']);
            $product->setOriginalPrice($data['originalPrice'] ? (string)$data['originalPrice'] : null);
            $product->setStockThreshold($data['threshold']);
            $product->setDescription('Complément alimentaire de qualité premium.');
            $product->setCreatedAt(new \DateTime());
            $product->setUpdatedAt(new \DateTime());
            $manager->persist($product);
            $products[] = $product;
        }

        // Notes de test
        $ratingData = [
            [$products[0], $user1, 5],
            [$products[0], $user2, 4],
            [$products[1], $user2, 3],
            [$products[3], $user1, 5],
            [$products[6], $user2, 4],
        ];

        foreach ($ratingData as [$product, $author, $score]) {
            $rating = new Rating();
            $rating->setScore($score);
            $rating->setAuthor($author);
            $rating->setProduct($product);
            $manager->persist($rating);
        }

        // Commentaires de test sur les 3 premiers produits
        $commentData = [
            [$products[0], $user1, 'Excellente whey, goût vanille très agréable et bonne dissolution !', '-5 days'],
            [$products[0], $user2, 'Je commande depuis 6 mois, qualité constante. Je recommande.', '-2 days'],
            [$products[1], $user2, 'Bon produit mais le goût chocolat est un peu fort à mon goût.', '-10 days'],
            [$products[3], $user1, 'Super promo sur la créatine, efficacité au rendez-vous après 3 semaines.', '-1 days'],
            [$products[6], $user2, 'Livraison rapide, produit conforme à la description.', '-7 days'],
        ];

        foreach ($commentData as [$product, $author, $content, $ago]) {
            $comment = new Comment();
            $comment->setContent($content);
            $comment->setAuthor($author);
            $comment->setProduct($product);
            $comment->setCreatedAt(new \DateTime($ago));
            $manager->persist($comment);
        }

        $manager->flush();
    }
}
