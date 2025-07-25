<?php

namespace App\DTO;

/**
 * Class ProductUpdateDTO
 * @package App\DTO
 *
 * Data Transfer Object para a atualização de um produto existente.
 * As propriedades são anuláveis para permitir atualizações parciais.
 */
class ProductUpdateDTO
{
    public ?string $name;
    public ?string $description;
    public ?float $price;
    public ?string $category;
    public ?string $imageUrl;
    public ?int $stock;
    public ?int $reservedStock;
    // O sellerId geralmente não é alterado em uma atualização de produto,
    // mas pode ser incluído se houver essa necessidade.
    // Para este caso, vamos omitir, pois a verificação de propriedade já ocorre no SellerService.

    public function __construct(
        ?string $name = null,
        ?string $description = null,
        ?float $price = null,
        ?string $category = null,
        ?string $imageUrl = null,
        ?int $stock = null,
        ?int $reservedStock = null
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->category = $category;
        $this->imageUrl = $imageUrl;
        $this->stock = $stock;
        $this->reservedStock = $reservedStock;
    }

    /**
     * Cria um ProductUpdateDTO a partir de um array associativo.
     * Útil para converter dados de requisições.
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'] ?? null,
            $data['description'] ?? null,
            $data['price'] ?? null,
            $data['category'] ?? null,
            $data['imageUrl'] ?? null,
            $data['stock'] ?? null,
            $data['reservedStock'] ?? null
        );
    }
}