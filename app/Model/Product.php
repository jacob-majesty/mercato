<?php

namespace App\Model;

use DateTime;
use InvalidArgumentException;

/**
 * Class Product
 * @package App\Model
 *
 * Representa um produto no sistema.
 */
class Product
{
    private ?int $id;
    private string $name;
    private float $price;
    private string $category;
    private ?string $description;
    private ?string $imageUrl;
    private int $stock;
    private int $sellerId;
    private ?DateTime $createdAt;
    private ?DateTime $updatedAt;

    public function __construct(
        ?int $id,
        string $name,
        float $price, // Espera float
        string $category,
        ?string $description,
        ?string $imageUrl,
        int $stock,
        int $sellerId,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->category = $category;
        $this->description = $description;
        $this->imageUrl = $imageUrl;
        $this->stock = $stock;
        $this->sellerId = $sellerId;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function getSellerId(): int
    {
        return $this->sellerId;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    // Setters (para permitir atualização de propriedades)
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setPrice(float $price): void
    {
        if ($price < 0) {
            throw new InvalidArgumentException("Preço não pode ser negativo.");
        }
        $this->price = $price;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function setImageUrl(?string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    public function setStock(int $stock): void
    {
        if ($stock < 0) {
            throw new InvalidArgumentException("Estoque não pode ser negativo.");
        }
        $this->stock = $stock;
    }

    public function setSellerId(int $sellerId): void
    {
        $this->sellerId = $sellerId;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Verifica se há estoque suficiente para uma dada quantidade.
     * @param int $quantity
     * @return bool
     */
    public function checkStock(int $quantity): bool
    {
        // Se você tiver um campo reserved_stock no futuro, ajuste aqui
        // return ($this->stock - $this->reservedStock) >= $quantity;
        return $this->stock >= $quantity;
    }
}
