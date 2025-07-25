<?php

namespace App\Model;

/**
 * Class CartItem
 * @package App\Model
 *
 * Representa um único produto no carrinho de compras.
 * É um objeto de valor dentro do Cart, não necessariamente uma entidade persistida separadamente.
 */
class CartItem
{
    private int $productId;
    private string $productName; // Para exibir no carrinho e manter o nome histórico
    private float $unitPrice;    // Preço do produto no momento em que foi adicionado ao carrinho
    private int $quantity;

    public function __construct(int $productId, string $productName, float $unitPrice, int $quantity)
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException("A quantidade do item no carrinho deve ser maior que zero.");
        }
        $this->productId = $productId;
        $this->productName = $productName;
        $this->unitPrice = $unitPrice;
        $this->quantity = $quantity;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException("A quantidade do item no carrinho deve ser maior que zero.");
        }
        $this->quantity = $quantity;
    }

    public function getTotal(): float
    {
        return $this->unitPrice * $this->quantity;
    }
}