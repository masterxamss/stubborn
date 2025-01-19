<?php

namespace App\Entity;

use App\Repository\ProductsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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

    /**
     * @var Collection<int, Cart>
     */
    #[ORM\OneToMany(targetEntity: Cart::class, mappedBy: 'product')]
    private Collection $carts;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'product')]
    private Collection $orderItems;


    public function __construct()
    {
        $this->carts = new ArrayCollection();
        $this->orderItems = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Cart>
     */
    public function getCarts(): Collection
    {
        return $this->carts;
    }

    public function addCart(Cart $cart): static
    {
        if (!$this->carts->contains($cart)) {
            $this->carts->add($cart);
            $cart->setProduct($this);
        }

        return $this;
    }

    public function removeCart(Cart $cart): static
    {
        if ($this->carts->removeElement($cart)) {
            // set the owning side to null (unless already changed)
            if ($cart->getProduct() === $this) {
                $cart->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setProduct($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getProduct() === $this) {
                $orderItem->setProduct(null);
            }
        }

        return $this;
    }
}
