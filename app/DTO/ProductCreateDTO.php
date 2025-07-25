<?php

namespace App\DTO;

/**
 * Class ProductCreateDTO
 * @package App\DTO
 *
 * Data Transfer Object para a criação de um novo produto.
 */
class ProductCreateDTO
{
    public string $name;
    public string $description;
    public float $price;
    public string $category;
    public ?string $imageUrl;
    public int $stock;
    public int $sellerId; // O ID do vendedor que está criando o produto

    public function __construct(
        string $name,
        string $description,
        float $price,
        string $category,
        ?string $imageUrl,
        int $stock,
        int $sellerId
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->category = $category;
        $this->imageUrl = $imageUrl;
        $this->stock = $stock;
        $this->sellerId = $sellerId;
    }
}