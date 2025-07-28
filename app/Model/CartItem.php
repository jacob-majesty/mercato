<?php

namespace App\Model;

/**
 * Class CartItem
 * @package App\Model
 *
 * Representa um item individual dentro de um carrinho de compras.
 * É um objeto de valor dentro do Cart, não necessariamente uma entidade persistida separadamente.
 */
class CartItem
{
    private ?int $id; // Adicionado para corresponder ao DB
    private int $cartId; // Adicionado para corresponder ao DB
    private int $productId;
    private string $productName; // Para exibir no carrinho e manter o nome histórico
    private float $unitPrice;    // Preço do produto no momento em que foi adicionado ao carrinho
    private int $quantity;

    public function __construct(
        ?int $id, // Primeiro argumento: ID do item do carrinho (pode ser nulo para novos itens)
        int $cartId, // Segundo argumento: ID do carrinho ao qual pertence
        int $productId,
        int $quantity,
        float $unitPrice,
        string $productName // Adicionado ao construtor
    ) {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException("A quantidade do item no carrinho deve ser maior que zero.");
        }
        $this->id = $id; // Atribui o ID
        $this->cartId = $cartId; // Atribui o Cart ID
        $this->productId = $productId;
        $this->productName = $productName;
        $this->unitPrice = $unitPrice;
        $this->quantity = $quantity;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCartId(): int
    {
        return $this->cartId;
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

    public function getTotal(): float
    {
        return $this->unitPrice * $this->quantity;
    }

    // Setters (se necessário)
    public function setId(?int $id): void // Permite definir o ID (ex: após salvar no DB)
    {
        $this->id = $id;
    }

    public function setCartId(int $cartId): void
    {
        $this->cartId = $cartId;
    }

    public function setProductId(int $productId): void
    {
        $this->productId = $productId;
    }

    public function setProductName(string $productName): void
    {
        $this->productName = $productName;
    }

    public function setUnitPrice(float $unitPrice): void
    {
        $this->unitPrice = $unitPrice;
    }

    public function setQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException("A quantidade do item no carrinho deve ser maior que zero.");
        }
        $this->quantity = $quantity;
    }
}
