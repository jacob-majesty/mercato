<?php

namespace App\Interfaces; 

use App\Model\Product;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;
    public function findAll(): array;
    public function save(Product $product): Product;
    public function update(Product $product): bool;
    public function delete(int $id): bool;
    // public function findBySellerId(int $sellerId): array; // REMOVIDO: Duplicado com getProductsBySellerId

    /**
     * Busca produtos por um ID de vendedor específico.
     * @param int $sellerId O ID do vendedor.
     * @return Product[] Uma array de objetos Product.
     */
    public function getProductsBySellerId(int $sellerId): array;

    /**
     * Busca produtos com base em critérios de filtro.
     *
     * @param string|null $searchTerm Termo para buscar no nome ou descrição.
     * @param string|null $category Categoria do produto.
     * @param float|null $minPrice Preço mínimo (inclusive).
     * @param float|null $maxPrice Preço máximo (inclusive).
     * @return Product[] Um array de objetos Product que correspondem aos critérios.
     */
    public function searchProducts(
        ?string $searchTerm = null,
        ?string $category = null,
        ?float $minPrice = null,
        ?float $maxPrice = null
    ): array;

     /**
     * Busca produtos com paginação.
     * @param int $limit Número máximo de produtos por página.
     * @param int $offset Número de produtos a pular (início da página).
     * @return Product[]
     */
    public function findPaginated(int $limit, int $offset): array;

    /**
     * Conta o número total de produtos.
     * @return int
     */
    public function countAll(): int;
}
