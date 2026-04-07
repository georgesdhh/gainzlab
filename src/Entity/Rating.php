<?php

namespace App\Entity;

use App\Repository\RatingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RatingRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_user_product_rating', columns: ['author_id', 'product_id'])]
class Rating
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $score = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'ratings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Product $product = null;

    public function getId(): ?int { return $this->id; }

    public function getScore(): ?int { return $this->score; }

    public function setScore(int $score): static { $this->score = $score; return $this; }

    public function getAuthor(): ?User { return $this->author; }

    public function setAuthor(?User $author): static { $this->author = $author; return $this; }

    public function getProduct(): ?Product { return $this->product; }

    public function setProduct(?Product $product): static { $this->product = $product; return $this; }
}
