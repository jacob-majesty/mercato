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
    private int $reserved; // Nova propriedade para estoque reservado
    private ?DateTime $reservedAt; // Nova propriedade para o timestamp da reserva
    private int $sellerId;
    private ?DateTime $createdAt;
    private ?DateTime $updatedAt;

    public function __construct(
        ?int $id,
        string $name,
        float $price,
        string $category,
        ?string $description,
        ?string $imageUrl,
        int $stock,
        int $sellerId,
        int $reserved = 0, // Valor padrão para reserved
        ?DateTime $reservedAt = null, // Valor padrão para reservedAt
        ?DateTime $createdAt = null, // Pode ser null para novos produtos
        ?DateTime $updatedAt = null // Pode ser null para novos produtos
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->category = $category;
        $this->description = $description;
        $this->imageUrl = $imageUrl;
        $this->stock = $stock;
        $this->sellerId = $sellerId;
        $this->reserved = $reserved;
        $this->reservedAt = $reservedAt;
        $this->createdAt = $createdAt ?? new DateTime(); // Define se não for fornecido
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

    public function getReserved(): int
    {
        return $this->reserved;
    }

    public function getReservedAt(): ?DateTime
    {
        return $this->reservedAt;
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

    // Setters
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

    public function setReserved(int $reserved): void
    {
        if ($reserved < 0) {
            throw new InvalidArgumentException("Estoque reservado não pode ser negativo.");
        }
        $this->reserved = $reserved;
    }

    public function setReservedAt(?DateTime $reservedAt): void
    {
        $this->reservedAt = $reservedAt;
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
     * Verifica se há estoque disponível (não reservado) suficiente para uma dada quantidade.
     * @param int $quantity
     * @return bool
     */
    public function checkStock(int $quantity): bool
    {
        return ($this->stock - $this->reserved) >= $quantity;
    }

    /**
     * Reserva uma quantidade do produto.
     *
     * @param int $quantity A quantidade a ser reservada.
     * @throws InvalidArgumentException Se a quantidade for negativa ou exceder o estoque disponível.
     */
    public function reserve(int $quantity): void
    {
        if ($quantity < 0) {
            throw new InvalidArgumentException("Quantidade a reservar não pode ser negativa.");
        }
        if ($quantity > ($this->stock - $this->reserved)) {
            throw new InvalidArgumentException("Quantidade a reservar excede o estoque disponível.");
        }
        $this->reserved += $quantity;
        $this->reservedAt = new DateTime(); // Atualiza o timestamp da reserva
    }

    /**
     * Libera uma quantidade reservada do produto.
     *
     * @param int $quantity A quantidade a ser liberada.
     * @throws InvalidArgumentException Se a quantidade for negativa ou exceder o estoque reservado.
     */
    public function release(int $quantity): void
    {
        if ($quantity < 0) {
            throw new InvalidArgumentException("Quantidade a liberar não pode ser negativa.");
        }
        if ($quantity > $this->reserved) {
            throw new InvalidArgumentException("Quantidade a liberar excede o estoque reservado.");
        }
        $this->reserved -= $quantity;
        // Se não houver mais reservas, limpa o timestamp
        if ($this->reserved === 0) {
            $this->reservedAt = null;
        }
    }

    /**
     * Decrementa o estoque total de um produto após uma compra confirmada.
     * Esta função também resolve a reserva correspondente.
     *
     * @param int $quantity A quantidade a ser decrementada.
     * @throws InvalidArgumentException Se a quantidade a decrementar for maior que o estoque total disponível.
     */
    public function decrementStock(int $quantity): void
    {
        if ($quantity < 0) {
            throw new InvalidArgumentException("Quantidade a decrementar não pode ser negativa.");
        }
        // Verifica se há estoque suficiente, incluindo o reservado que será "consumido"
        if ($quantity > $this->stock) {
            throw new InvalidArgumentException("Quantidade a decrementar excede o estoque total.");
        }

        $this->stock -= $quantity;

        // Se houver reservas e a quantidade comprada for menor ou igual à reservada,
        // diminui a reserva. Se for maior, zera a reserva.
        if ($this->reserved > 0) {
            $this->reserved = max(0, $this->reserved - $quantity);
            if ($this->reserved === 0) {
                $this->reservedAt = null;
            }
        }
    }

    /**
     * Incrementa o estoque total de um produto.
     * @param int $quantity A quantidade a ser incrementada.
     */
    public function incrementStock(int $quantity): void
    {
        if ($quantity < 0) {
            throw new InvalidArgumentException("Quantidade a incrementar não pode ser negativa.");
        }
        $this->stock += $quantity;
    }
}
