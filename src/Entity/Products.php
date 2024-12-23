<?php

namespace App\Entity;

use App\Repository\ProductsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Event\LifecycleEventArgs;

#[ORM\Entity(repositoryClass: ProductsRepository::class)]
class Products
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du produit ne peut pas être vide.")]
    #[Assert\Regex(['pattern' => '/^[a-zA-ZÀ-ÖØ-öø-ÿ\s]+$/', 'message' => 'Le nom ne doit contenir que des lettres et des espaces.',])]
    private ?string $name = null;

    #[ORM\Column]
    private array $stock = [
        'XS' => 0,
        'S' => 0,
        'M' => 0,
        'L' => 0,
        'XL' => 0
    ];

    #[ORM\Column]
    private ?bool $highLighted = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column]
    #[Assert\NotBlank(['message' => 'Ce champ ne peut pas être vide.'])]
    #[Assert\Positive(['message' => 'Le prix doit être positif.'])]
    #[Assert\Type(['type' => 'float', 'message' => 'Ce champ doit être un nombre.'])]
    private ?float $price = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getStock(): array
    {
        return $this->stock ?? [
            'XS' => 0,
            'S' => 0,
            'M' => 0,
            'L' => 0,
            'XL' => 0
        ];
    }

    public function setStock(array $stock): static
    {
        $defaultStock = [
            'XS' => 0,
            'S' => 0,
            'M' => 0,
            'L' => 0,
            'XL' => 0
        ];

        $this->stock = array_merge($defaultStock, $stock);

        return $this;
    }

    public function getStockForSize(string $size): int
    {
        return $this->stock[$size] ?? 0;
    }

    public function setStockForSize(string $size, int $value): self
    {
        if (isset($this->stock[$size])) {
            $this->stock[$size] = $value;
        }

        return $this;
    }

    public function isHighLighted(): ?bool
    {
        return $this->highLighted;
    }

    public function setHighLighted(bool $highLighted): static
    {
        $this->highLighted = $highLighted;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }
}
