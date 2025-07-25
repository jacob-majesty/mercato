<?php

namespace App\Model;

use DateTime;

/**
 * Class Product
 * @package App\Model
 *
 * Representa um produto ou ingresso disponível para venda.
 */
class Product
{
    private ?int $id;
    private string $name;
    private float $price;
    private string $category;
    private string $description;
    private string $imageUrl;
    private int $stock;
    private int $sellerId; // ID do vendedor que publicou o produto
    private ?int $reserved; // Quantidade reservada por clientes (último item)
    private ?DateTime $reservedAt; // Timestamp da reserva do último item

    /**
     * Construtor da classe Product.
     *
     * @param int|null $id O ID do produto (nulo para novos produtos).
     * @param string $name O nome do produto.
     * @param float $price O preço do produto.
     * @param string $category A categoria do produto.
     * @param string $description A descrição do produto.
     * @param string $imageUrl A URL da imagem do produto.
     * @param int $stock A quantidade total em estoque.
     * @param int $sellerId O ID do vendedor que publicou o produto.
     * @param int $reserved Quantidade de itens reservados (padrão 0).
     * @param DateTime|null $reservedAt Timestamp da reserva (nulo se não houver reserva).
     */
    public function __construct(
        ?int $id,
        string $name,
        float $price,
        string $category,
        string $description,
        string $imageUrl,
        int $stock,
        int $sellerId,
        int $reserved = 0,
        ?DateTime $reservedAt = null
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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getImageUrl(): string
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

    public function getReserved(): int
    {
        return $this->reserved;
    }

    public function getReservedAt(): ?DateTime
    {
        return $this->reservedAt;
    }

    // Setters (para atributos que podem ser alterados após a criação)
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setImageUrl(string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    public function setStock(int $stock): void
    {
        $this->stock = $stock;
    }

    /**
     * Verifica se há estoque disponível para uma determinada quantidade.
     * Considera o estoque total menos a quantidade reservada.
     *
     * @param int $quantity A quantidade desejada.
     * @return bool True se houver estoque suficiente, false caso contrário.
     */
    public function checkStock(int $quantity): bool
    {
        // Regra: Só podem ser comprados se `quantidade - reservado > 0`.
        return ($this->stock - $this->reserved) >= $quantity;
    }

    /**
     * Reserva uma quantidade do produto.
     * Regra: Se for o último item, ele é reservado por 2 minutos.
     * Esta lógica pode ser orquestrada no Service (ex: CompraService)
     * que chamaria este método do Model.
     *
     * @param int $quantity A quantidade a ser reservada.
     * @return bool True se a reserva for bem-sucedida, false caso contrário (ex: estoque insuficiente).
     */
    public function reserve(int $quantity): bool
    {
        if ($this->checkStock($quantity)) {
            // Lógica de reserva. Se for o último item (ou se a reserva vai zerar o estoque disponível),
            // a lógica de tempo pode ser aplicada no Service.
            // Aqui, apenas incrementamos a reserva.
            $this->reserved += $quantity;
            if (($this->stock - $this->reserved) === 0) {
                $this->reservedAt = new DateTime(); // Marca a hora da reserva do último item
            }
            return true;
        }
        return false;
    }

    /**
     * Libera uma quantidade reservada do produto.
     * @param int $quantity A quantidade a ser liberada.
     * @return void
     */
    public function release(int $quantity): void
    {
        $this->reserved = max(0, $this->reserved - $quantity);
        if ($this->reserved === 0) {
            $this->reservedAt = null; // Remove o timestamp se não houver mais reservas
        }
    }

    /**
     * Decrementa o estoque do produto após uma compra.
     * @param int $quantity A quantidade a ser decrementada.
     * @return void
     */
    public function decrementStock(int $quantity): void
    {
        $this->stock -= $quantity;
        // Após a decrementação, também se libera a reserva correspondente.
        $this->release($quantity);
    }
}