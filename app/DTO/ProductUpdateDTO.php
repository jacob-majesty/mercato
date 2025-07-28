<?php

namespace App\DTO;

use InvalidArgumentException;

/**
 * Class ProductUpdateDTO
 * @package App\DTO
 *
 * DTO para atualização de um produto existente.
 * Permite a atualização parcial de campos.
 */
class ProductUpdateDTO
{
    public int $id; // O ID do produto é obrigatório para identificar qual produto será atualizado
    public ?string $name;
    public ?float $price;
    public ?string $category;
    public ?string $description;
    public ?string $imageUrl;
    public ?int $stock;

    /**
     * Construtor do DTO.
     * @param array $data Array associativo contendo os dados do produto a serem atualizados.
     * Campos podem ser null se não forem ser atualizados.
     * @throws InvalidArgumentException Se o ID do produto estiver faltando.
     */
    public function __construct(array $data)
    {
        if (!isset($data['id']) || !is_numeric($data['id']) || (int)$data['id'] <= 0) {
            throw new InvalidArgumentException("ID do produto é obrigatório para atualização.");
        }
        $this->id = (int)$data['id'];

        // Atribui os valores, permitindo que sejam null se não fornecidos no array $data
        $this->name = isset($data['name']) && !empty($data['name']) ? trim($data['name']) : null;

        if (isset($data['price'])) {
            if (!is_numeric($data['price']) || (float)$data['price'] <= 0) {
                throw new InvalidArgumentException("Preço do produto deve ser um número positivo.");
            }
            $this->price = (float)$data['price'];
        } else {
            $this->price = null;
        }

        $this->category = isset($data['category']) && !empty($data['category']) ? trim($data['category']) : null;
        $this->description = isset($data['description']) && !empty($data['description']) ? trim($data['description']) : null;
        $this->imageUrl = isset($data['image_url']) && !empty($data['image_url']) ? trim($data['image_url']) : null; // Usa 'image_url'

        if (isset($data['stock'])) {
            if (!is_numeric($data['stock']) || (int)$data['stock'] < 0) {
                throw new InvalidArgumentException("Estoque do produto deve ser um número inteiro não negativo.");
            }
            $this->stock = (int)$data['stock'];
        } else {
            $this->stock = null;
        }
    }
}
