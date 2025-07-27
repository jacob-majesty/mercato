<?php

namespace App\DTO;

use InvalidArgumentException;

/**
 * Class ProductCreateDTO
 * @package App\DTO
 *
 * DTO para criação de um novo produto.
 * Encapsula e valida os dados de entrada do formulário de criação.
 */
class ProductCreateDTO
{
    public string $name;
    public float $price;
    public string $category;
    public ?string $description;
    public ?string $imageUrl;
    public int $stock;
    public int $sellerId;

    /**
     * Construtor do DTO.
     * @param array $data Array associativo contendo os dados do produto.
     * @throws InvalidArgumentException Se algum dado obrigatório estiver faltando ou for inválido.
     */
    public function __construct(array $data)
    {
        // Validação e atribuição do Nome
        if (empty($data['name'])) {
            throw new InvalidArgumentException("Nome do produto é obrigatório.");
        }
        $this->name = trim($data['name']);

        // Validação e atribuição do Preço
        // Verifica se 'price' existe, não é vazio e é um número válido maior que zero.
        if (!isset($data['price']) || !is_numeric($data['price']) || (float)$data['price'] <= 0) {
            throw new InvalidArgumentException("Preço do produto é obrigatório e deve ser um número positivo.");
        }
        $this->price = (float)$data['price'];

        // Validação e atribuição da Categoria
        if (empty($data['category'])) {
            throw new InvalidArgumentException("Categoria do produto é obrigatória.");
        }
        $this->category = trim($data['category']);

        // Descrição é opcional
        $this->description = !empty($data['description']) ? trim($data['description']) : null;

        // URL da Imagem é opcional
        $this->imageUrl = !empty($data['image_url']) ? trim($data['image_url']) : null; // Usa 'image_url' conforme o formulário

        // Validação e atribuição do Estoque
        // Verifica se 'stock' existe, não é vazio e é um número inteiro não negativo.
        if (!isset($data['stock']) || !is_numeric($data['stock']) || (int)$data['stock'] < 0) {
            throw new InvalidArgumentException("Estoque do produto é obrigatório e deve ser um número inteiro não negativo.");
        }
        $this->stock = (int)$data['stock'];

        // Validação e atribuição do ID do Vendedor
        if (!isset($data['sellerId']) || !is_numeric($data['sellerId']) || (int)$data['sellerId'] <= 0) {
            throw new InvalidArgumentException("ID do vendedor é obrigatório e deve ser um número positivo.");
        }
        $this->sellerId = (int)$data['sellerId'];
    }
}
