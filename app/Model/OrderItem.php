<?php

namespace App\Model;

/**
 * Class OrderItem
 * @package App\Model
 *
 * Representa um item específico dentro de uma ordem de compra.
 */
class OrderItem
{
    private ?int $id;
    private ?int $orderId; 
    private int $productId;
    private string $productName; // Para histórico, caso o nome do produto mude
    private int $quantity;
    private float $unitPrice; // Preço do produto no momento da compra

    /**
     * Construtor da classe OrderItem.
     *
     * @param int|null $id O ID do item da ordem.
     * @param int|null $orderId O ID da ordem a qual este item pertence (pode ser nulo inicialmente).
     * @param int $productId O ID do produto.
     * @param string $productName O nome do produto.
     * @param int $quantity A quantidade do produto.
     * @param float $unitPrice O preço unitário do produto no momento da compra.
     */
    public function __construct(
        ?int $id,
        ?int $orderId, 
        int $productId,
        string $productName,
        int $quantity,
        float $unitPrice
    ) {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->productId = $productId;
        $this->productName = $productName;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): ?int // Getter também corrigido para nullable
    {
        return $this->orderId;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    // Setters (útil para definir o orderId após a ordem ser salva)
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setOrderId(?int $orderId): void // Setter para orderId adicionado
    {
        $this->orderId = $orderId;
    }

    public function setProductId(int $productId): void
    {
        $this->productId = $productId;
    }

    public function setProductName(string $productName): void
    {
        $this->productName = $productName;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function setUnitPrice(float $unitPrice): void
    {
        $this->unitPrice = $unitPrice;
    }

    /**
     * Calcula o valor total deste item (quantidade * preço unitário).
     * @return float O valor total do item.
     */
    public function getTotal(): float
    {
        return $this->quantity * $this->unitPrice;
    }
}