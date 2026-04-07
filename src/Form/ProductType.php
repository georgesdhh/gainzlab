<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
                'constraints' => [new NotBlank()],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix (€)',
                'currency' => 'EUR',
                'constraints' => [new Positive()],
            ])
            ->add('originalPrice', MoneyType::class, [
                'label' => 'Prix original (€) — barré si promo',
                'currency' => 'EUR',
                'required' => false,
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock',
                'constraints' => [new Positive()],
            ])
            ->add('stockThreshold', IntegerType::class, [
                'label' => 'Seuil alerte stock',
                'data' => 5,
            ])
            ->add('country', TextType::class, [
                'label' => 'Pays d\'origine',
                'required' => false,
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'required' => false,
                'placeholder' => '-- Choisir --',
                'choices' => [
                    'Protéines'       => 'Protéines',
                    'Créatine'        => 'Créatine',
                    'Acides Aminés'   => 'Acides Aminés',
                    'Vitamines'       => 'Vitamines',
                    'Brûleurs'        => 'Brûleurs',
                    'Barres & Snacks' => 'Barres & Snacks',
                    'Hydratation'     => 'Hydratation',
                ],
            ])
            ->add('promoTag', ChoiceType::class, [
                'label' => 'Tag promo',
                'required' => false,
                'placeholder' => '-- Aucun --',
                'choices' => [
                    'Nouveau'         => 'Nouveau',
                    'Promo'           => 'Promo',
                    'Bestseller'      => 'Bestseller',
                    'Édition Limitée' => 'Édition Limitée',
                ],
            ])
            ->add('expirationDate', DateType::class, [
                'label'    => 'Date d\'expiration',
                'required' => false,
                'widget'   => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}