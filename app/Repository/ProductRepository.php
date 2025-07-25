<?php

namespace App\Repository;

use App\Model\Product;
use PDO;
use DateTime;

class ProductRepository implements ProductRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function mapProduct(array $data): Product
    {
        return new Product(
            $data['id_produto'],
            $data['nome'],
            (float)$data['preco'],
            $data['categoria'],
            $data['descricao'],
            $data['url_imagem'],
            (int)$data['estoque'],
            (int)$data['id_vendedor'],
            (int)$data['reservado'] ?? 0,
            isset($data['reservado_em']) ? new DateTime($data['reservado_em']) : null
        );
    }

    public function findById(int $id): ?Product
    {
        $stmt = $this->pdo->prepare("SELECT * FROM produtos WHERE id_produto = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->mapProduct($data) : null;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM produtos");
        $products = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $data) {
            $products[] = $this->mapProduct($data);
        }
        return $products;
    }

    public function save(Product $product): Product
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO produtos (nome, preco, categoria, descricao, url_imagem, estoque, id_vendedor, reservado, reservado_em)
             VALUES (:nome, :preco, :categoria, :descricao, :url_imagem, :estoque, :id_vendedor, :reservado, :reservado_em)"
        );
        $stmt->execute([
            ':nome' => $product->getName(),
            ':preco' => $product->getPrice(),
            ':categoria' => $product->getCategory(),
            ':descricao' => $product->getDescription(),
            ':url_imagem' => $product->getImageUrl(),
            ':estoque' => $product->getStock(),
            ':id_vendedor' => $product->getSellerId(),
            ':reservado' => $product->getReserved(),
            ':reservado_em' => $product->getReservedAt() ? $product->getReservedAt()->format('Y-m-d H:i:s') : null,
        ]);
        // Para uma implementação completa, o Product Model precisaria de um setId()
        // Ou o save deveria retornar um novo objeto Product com o ID populado.
        // Para simplificar, vou simular o retorno do ID ou buscar o item.
        $reflection = new \ReflectionProperty($product, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($product, (int)$this->pdo->lastInsertId());
        return $product;
    }

    public function update(Product $product): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE produtos SET nome = :nome, preco = :preco, categoria = :categoria,
             descricao = :descricao, url_imagem = :url_imagem, estoque = :estoque,
             reservado = :reserved, reservado_em = :reserved_at WHERE id_produto = :id"
        );
        return $stmt->execute([
            ':nome' => $product->getName(),
            ':preco' => $product->getPrice(),
            ':categoria' => $product->getCategory(),
            ':descricao' => $product->getDescription(),
            ':url_imagem' => $product->getImageUrl(),
            ':estoque' => $product->getStock(),
            ':reserved' => $product->getReserved(),
            ':reserved_at' => $product->getReservedAt() ? $product->getReservedAt()->format('Y-m-d H:i:s') : null,
            ':id' => $product->getId()
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM produtos WHERE id_produto = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function findBySellerId(int $sellerId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM produtos WHERE id_vendedor = :sellerId");
        $stmt->execute([':sellerId' => $sellerId]);
        $products = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $data) {
            $products[] = $this->mapProduct($data);
        }
        return $products;
    }

     /**
     * Busca produtos por um ID de vendedor específico.
     * @param int $sellerId O ID do vendedor.
     * @return Product[] Uma array de objetos Product.
     */
    public function getProductsBySellerId(int $sellerId): array
    {
        $sql = "SELECT id, name, description, price, category, image_url, stock, reserved_stock, seller_id, created_at FROM products WHERE seller_id = :sellerId ORDER BY name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':sellerId', $sellerId, PDO::PARAM_INT);
        $stmt->execute();
        $productsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $products = [];
        foreach ($productsData as $productData) {
            $products[] = $this->hydrateProduct($productData);
        }
        return $products;
    }

     /**
     * Cria um objeto Product a partir dos dados do banco de dados.
     * @param array $data Dados do produto.
     * @return Product
     */
    private function hydrateProduct(array $data): Product
    {
        return new Product(
            $data['id'],
            $data['name'],
            $data['description'],
            (float)$data['price'],
            $data['category'],
            (int)$data['stock'],
            (int)$data['reserved_stock'],
            $data['seller_id'],
            $data['image_url'],
            new DateTime($data['created_at'])
        );
    }

     /**
     * @inheritDoc
     */
    public function searchProducts(
        ?string $searchTerm = null,
        ?string $category = null,
        ?float $minPrice = null,
        ?float $maxPrice = null
    ): array {
        $sql = "SELECT * FROM products WHERE 1=1";

        $params = [];

        if ($searchTerm !== null && $searchTerm !== '') {
            $sql .= " AND (name LIKE :searchTerm OR description LIKE :searchTerm)";
            $params[':searchTerm'] = '%' . $searchTerm . '%';
        }

        if ($category !== null && $category !== '') {
            $sql .= " AND category = :category";
            $params[':category'] = $category;
        }

        if ($minPrice !== null && $minPrice >= 0) {
            $sql .= " AND price >= :minPrice";
            $params[':minPrice'] = $minPrice;
        }

        if ($maxPrice !== null && $maxPrice >= 0 && ($minPrice === null || $maxPrice >= $minPrice)) {
            $sql .= " AND price <= :maxPrice";
            $params[':maxPrice'] = $maxPrice;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $products = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = new Product(
                $row['id'],
                $row['name'],
                $row['price'],
                $row['category'],
                $row['description'],
                $row['imageUrl'],
                $row['stock'],
                $row['sellerId'],
                $row['reserved'],
                $row['reservedAt'] ? new DateTime($row['reservedAt']) : null
            );
        }

        return $products;
    }

     /**
     * Implementação da busca paginada.
     * @param int $limit
     * @param int $offset
     * @return Product[]
     */
    public function findPaginated(int $limit, int $offset): array
    {
        // Certifique-se de que a ordem (ORDER BY) é consistente para paginação
        $sql = "SELECT * FROM products ORDER BY id ASC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $products = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = $this->hydrateProduct($data);
        }
        return $products;
    }

    /**
     * Implementação da contagem total de produtos.
     * @return int
     */
    public function countAll(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM products");
        return (int) $stmt->fetchColumn();
    }
}