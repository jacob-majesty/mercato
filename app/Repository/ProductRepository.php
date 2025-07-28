<?php

namespace App\Repository;

use PDO;
use App\Model\Product;
use App\Interfaces\ProductRepositoryInterface; // Importa a interface
use DateTime;
use Exception;

/**
 * Class ProductRepository
 * @package App\Repository
 *
 * Implementação concreta de ProductRepositoryInterface para MySQL.
 */
class ProductRepository implements ProductRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?Product
    {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->mapToProduct($data);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM products");
        $products = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = $this->mapToProduct($data);
        }
        return $products;
    }

    public function findPaginated(int $limit, int $offset): array
    {
        // Garante que a query seleciona todas as colunas necessárias para o modelo Product
        $stmt = $this->pdo->prepare("SELECT id, name, description, price, stock, image_url, category, seller_id, created_at, updated_at FROM products LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $products = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = $this->mapToProduct($data);
        }
        return $products;
    }

    public function countAll(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM products");
        return (int) $stmt->fetchColumn();
    }

    public function getProductsBySellerId(int $sellerId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE seller_id = :seller_id");
        $stmt->execute(['seller_id' => $sellerId]);
        $products = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = $this->mapToProduct($data);
        }
        return $products;
    }

    public function save(Product $product): Product
    {
        $sql = "INSERT INTO products (name, description, price, stock, image_url, category, seller_id, created_at) VALUES (:name, :description, :price, :stock, :image_url, :category, :seller_id, :created_at)";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'stock' => $product->getStock(),
            'image_url' => $product->getImageUrl(),
            'category' => $product->getCategory(),
            'seller_id' => $product->getSellerId(),
            'created_at' => $product->getCreatedAt() ? $product->getCreatedAt()->format('Y-m-d H:i:s') : (new DateTime())->format('Y-m-d H:i:s')
        ]);

        $product->setId((int)$this->pdo->lastInsertId());
        return $product;
    }

    public function update(Product $product): bool
    {
        $sql = "UPDATE products SET name = :name, description = :description, price = :price, stock = :stock, image_url = :image_url, category = :category, seller_id = :seller_id, updated_at = :updated_at WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'stock' => $product->getStock(),
            'image_url' => $product->getImageUrl(),
            'category' => $product->getCategory(),
            'seller_id' => $product->getSellerId(),
            'updated_at' => (new DateTime())->format('Y-m-d H:i:s') // Atualiza o timestamp
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Busca produtos com base em critérios de filtro.
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
    ): array {
        $sql = "SELECT * FROM products WHERE 1=1"; // Começa com uma condição verdadeira

        $params = [];

        if ($searchTerm) {
            $sql .= " AND (name LIKE :searchTerm OR description LIKE :searchTerm)";
            $params[':searchTerm'] = '%' . $searchTerm . '%';
        }

        if ($category) {
            $sql .= " AND category = :category";
            $params[':category'] = $category;
        }

        if ($minPrice !== null) {
            $sql .= " AND price >= :minPrice";
            $params[':minPrice'] = $minPrice;
        }

        if ($maxPrice !== null) {
            $sql .= " AND price <= :maxPrice";
            $params[':maxPrice'] = $maxPrice;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $products = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = $this->mapToProduct($data);
        }
        return $products;
    }

    /**
     * Mapeia um array de dados do banco de dados para um objeto Product.
     * @param array $data
     * @return Product
     */
    private function mapToProduct(array $data): Product
    {
        // Atribui cada valor a uma variável para garantir a ordem e o tipo corretos
        $id = (int)$data['id'];
        $name = $data['name'];
        $price = (float)$data['price'];
        $category = $data['category'];
        $description = $data['description'];
        $imageUrl = $data['image_url'];
        $stock = (int)$data['stock'];
        $sellerId = (int)$data['seller_id'];
        $reserved = (int)($data['reserved'] ?? 0); 
        $reservedAt = isset($data['reserved_at']) && $data['reserved_at'] !== null ? new DateTime($data['reserved_at']) : null; 
        $createdAt = new DateTime($data['created_at']); 
        $updatedAt = new DateTime($data['updated_at']); 

        return new Product(
            $id,
            $name,
            $price,
            $category,
            $description,
            $imageUrl,
            $stock,
            $sellerId,
            $reserved,
            $reservedAt,
            $createdAt,
            $updatedAt
        );
    }
}
