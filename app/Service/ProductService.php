<?php

namespace App\Service;

use App\Interfaces\ProductRepositoryInterface;
use App\Model\Product;
use App\DTO\ProductCreateDTO;
use App\DTO\ProductUpdateDTO;
use Exception;
use DateTime;

class ProductService
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getProductById(int $id): ?Product
    {
        return $this->productRepository->findById($id);
    }

    /**
     * Obtém todos os produtos cadastrados por um vendedor específico.
     * @param int $sellerId O ID do vendedor.
     * @return Product[]
     */
    public function getProductsBySellerId(int $sellerId): array
    {
        return $this->productRepository->getProductsBySellerId($sellerId);
    }

    public function getAvailableProducts(): array
    {
        $allProducts = $this->productRepository->findAll();
        $availableProducts = [];
        foreach ($allProducts as $product) {
            // Usa o método checkStock do modelo Product para verificar disponibilidade para 1 unidade
            if ($product->checkStock(1)) {
                $availableProducts[] = $product;
            }
        }
        return $availableProducts;
    }

    /**
     * Cria um novo produto a partir de um DTO.
     * @param ProductCreateDTO $productDTO
     * @return Product
     * @throws Exception Se os dados forem inválidos ou a persistência falhar.
     */
     /**
     * Cria um novo produto no sistema.
     * @param ProductCreateDTO $productDTO Dados para criação do produto.
     * @return Product O objeto Product recém-criado.
     * @throws Exception Se a persistência falhar.
     */
    public function createProduct(ProductCreateDTO $productDTO): Product
    {
        // Cria uma nova instância de Product Model
        $product = new Product(
            null, // ID será gerado pelo banco de dados
            $productDTO->name,
            $productDTO->price,
            $productDTO->category,
            $productDTO->description,
            $productDTO->imageUrl,
            $productDTO->stock,
            $productDTO->sellerId,
            0, // reserved
            null, // reserved_at
            new DateTime(), // created_at
            new DateTime() // updated_at
        );

        try {
            return $this->productRepository->save($product);
        } catch (Exception $e) {
            error_log("ProductService: Erro ao criar produto: " . $e->getMessage());
            throw new Exception("Falha ao criar produto: " . $e->getMessage());
        }
    }

    /**
     * Atualiza um produto existente a partir de um DTO.
     * @param int $productId
     * @param ProductUpdateDTO $productDTO
     * @return bool
     * @throws Exception Se o produto não for encontrado ou a atualização falhar.
     */
    public function updateProduct(ProductUpdateDTO $productDTO): bool
    {
        $product = $this->productRepository->findById($productDTO->id);
        if (!$product) {
            throw new Exception("Produto com ID {$productDTO->id} não encontrado para atualização.");
        }

        // Aplica as atualizações apenas se os dados estiverem presentes no DTO
        if ($productDTO->name !== null) {
            $product->setName($productDTO->name);
        }
        if ($productDTO->price !== null) {
            $product->setPrice($productDTO->price);
        }
        if ($productDTO->category !== null) {
            $product->setCategory($productDTO->category);
        }
        if ($productDTO->description !== null) {
            $product->setDescription($productDTO->description);
        }
        if ($productDTO->imageUrl !== null) {
            $product->setImageUrl($productDTO->imageUrl);
        }
        if ($productDTO->stock !== null) {
            $product->setStock($productDTO->stock);
        }
        // O sellerId não deve ser alterado aqui, pois um produto pertence a um vendedor.

        try {
            return $this->productRepository->update($product);
        } catch (Exception $e) {
            error_log("ProductService: Erro ao atualizar produto ID {$productDTO->id}: " . $e->getMessage());
            throw new Exception("Falha ao atualizar produto: " . $e->getMessage());
        }
    }

    public function deleteProduct(int $productId): bool
    {
        return $this->productRepository->delete($productId);
    }

    /**
     * Verifica se há estoque disponível para um produto.
     * Utiliza o método `checkStock` do modelo `Product`.
     *
     * @param int $productId O ID do produto.
     * @param int $quantity A quantidade desejada.
     * @return bool True se houver estoque disponível, false caso contrário.
     * @throws Exception Se o produto não for encontrado.
     */
    public function checkProductStock(int $productId, int $quantity): bool
    {
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            return false; // Ou lançar uma exceção específica se preferir
        }
        return $product->checkStock($quantity); 
    }

        /**
     * Reserva uma quantidade de um produto.
     * @param int $productId O ID do produto.
     * @param int $quantity A quantidade a ser reservada.
     * @return bool True se a reserva foi bem-sucedida.
     * @throws Exception Se o produto não for encontrado ou estoque insuficiente para reserva.
     */
    public function reserveStock(int $productId, int $quantity): bool
    {
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw new Exception("Produto não encontrado para reserva.");
        }

        // Chama o método 'void' no modelo. Ele lançará uma exceção em caso de falha.
        // Se não lançar, significa que a reserva foi feita com sucesso no objeto em memória.
        $product->reserve($quantity); 
        
        // Persiste as mudanças no banco de dados. O retorno é o sucesso da operação de persistência.
        return $this->productRepository->update($product);
    }

    /**
     * Libera uma quantidade de estoque reservado, movendo-o de volta para o estoque disponível.
     * Utiliza o método `release` do modelo `Product`.
     * @param int $productId O ID do produto.
     * @param int $quantity A quantidade a ser liberada.
     * @return bool True se a liberação foi bem-sucedida.
     * @throws Exception Se o produto não for encontrado.
     */
    public function releaseStock(int $productId, int $quantity): bool
    {
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw new Exception("Produto não encontrado para liberação de reserva.");
        }

        $product->release($quantity); 
        return $this->productRepository->update($product);
    }

    /**
     * Decrementa o estoque total de um produto após uma compra confirmada.
     * Utiliza o método `decrementStock` do modelo `Product`, que já resolve a reserva correspondente.
     *
     * @param int $productId O ID do produto.
     * @param int $quantity A quantidade a ser decrementada.
     * @return bool True se o estoque foi decrementado com sucesso.
     * @throws Exception Se o produto não for encontrado ou estoque insuficiente.
     */
    public function decrementStock(int $productId, int $quantity): bool
    {
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw new Exception("Produto não encontrado para decremento de estoque.");
        }

        // Primeiro, verifica se há estoque total suficiente
        if ($product->getStock() < $quantity) {
             throw new Exception("Estoque insuficiente para decremento.");
        }

        $product->decrementStock($quantity); 
        return $this->productRepository->update($product);
    }

    /**
     * Incrementa o estoque de um produto (ex: em caso de devolução ou cancelamento de pedido).
     * @param int $productId O ID do produto.
     * @param int $quantity A quantidade a ser incrementada.
     * @return bool True se o estoque foi incrementado.
     * @throws Exception Se o produto não for encontrado.
     */
    public function incrementProductStock(int $productId, int $quantity): bool
    {
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw new Exception("Produto não encontrado.");
        }

        $product->setStock($product->getStock() + $quantity);
        return $this->productRepository->update($product);
    }

    /**
     * Aplica um desconto percentual a um produto.
     * @param int $productId O ID do produto.
     * @param float $discountPercent O percentual de desconto (ex: 0.10 para 10%).
     * @return bool True se o desconto foi aplicado com sucesso.
     * @throws Exception Se o produto não for encontrado ou o desconto for inválido.
     */
    public function applyDiscountToProduct(int $productId, float $discountPercent): bool
    {
        if ($discountPercent < 0 || $discountPercent > 1) {
            throw new Exception("O percentual de desconto deve estar entre 0 e 1.");
        }

        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw new Exception("Produto não encontrado para aplicar desconto.");
        }

        $newPrice = $product->getPrice() * (1 - $discountPercent);
        $product->setPrice($newPrice);

        return $this->productRepository->update($product);
    }

    /**
     * Libera reservas de "último item" que expiraram (após 2 minutos).
     * Este método deve ser chamado por um processo agendado (cron job)
     * para limpar reservas não confirmadas.
     *
     * @return int O número de reservas liberadas.
     */
    public function releaseExpiredLastItemReservations(): int
    {
        $releasedCount = 0;
        // Busca todos os produtos que possuem alguma reserva ativa
        // Em um ambiente de produção, seria ideal ter um método no Repository
        // que filtre produtos com 'reserved > 0' e 'reservedAt' preenchido para otimizar.
        $allProducts = $this->productRepository->findAll();
        $currentTime = new DateTime();

        foreach ($allProducts as $product) {
            // Verifica se há uma reserva e se ela tem um timestamp de reserva
            if ($product->getReserved() > 0 && $product->getReservedAt() !== null) { 
                $reservedTime = $product->getReservedAt(); 
                $expirationTime = (clone $reservedTime)->modify('+120 seconds'); // Adiciona 2 minutos

                if ($currentTime > $expirationTime) {
                    // Libera a quantidade reservada usando o método do modelo Product
                    $product->release($product->getReserved());
                    $this->productRepository->update($product);
                    $releasedCount++;
                }
            }
        }
        return $releasedCount;
    }
    
    /**
     * Filtra produtos com base em categoria, termo de busca e faixa de preço.
     * Delega a lógica de busca para o repositório.
     *
     * @param string|null $searchTerm Termo para buscar no nome ou descrição do produto.
     * @param string|null $category Categoria do produto (ex: "Eletrônicos", "Livros").
     * @param float|null $minPrice Preço mínimo do produto.
     * @param float|null $maxPrice Preço máximo do produto.
     * @return Product[] Um array de objetos Product que correspondem aos critérios de filtro.
     */
    public function filterProducts(
        ?string $searchTerm = null,
        ?string $category = null,
        ?float $minPrice = null,
        ?float $maxPrice = null
    ): array {
        return $this->productRepository->searchProducts(
            $searchTerm,
            $category,
            $minPrice,
            $maxPrice
        );
    }

     /**
     * Obtém produtos disponíveis com paginação.
     * @param int $page A página atual (começa em 1).
     * @param int $itemsPerPage O número de itens por página.
     * @return array Um array contendo 'products' e 'totalPages'.
     */
    public function getAvailableProductsPaginated(int $page, int $itemsPerPage): array
    {
        if ($page < 1) {
            $page = 1;
        }
        if ($itemsPerPage < 1) {
            $itemsPerPage = 10; // Valor padrão razoável
        }

        $offset = ($page - 1) * $itemsPerPage;

        // Primeiro, obtenha o total de itens para calcular o total de páginas
        $totalItems = $this->productRepository->countAll(); // Ou um método para contar apenas disponíveis

        // Calcule o total de páginas
        $totalPages = ceil($totalItems / $itemsPerPage);

        // Garante que a página não excede o total de páginas
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
            $offset = ($page - 1) * $itemsPerPage;
        }

        // Obtém os produtos para a página atual
        $products = $this->productRepository->findPaginated($itemsPerPage, $offset);

        // Filtrar produtos disponíveis (se `findPaginated` não fizer isso)
        $availableProducts = [];
        foreach ($products as $product) {
            if ($product->checkStock(1)) { // Verifica se há pelo menos 1 item disponível
                $availableProducts[] = $product;
            }
        }

        return [
            'products' => $availableProducts,
            'currentPage' => $page,
            'totalItems' => $totalItems,
            'totalPages' => (int) $totalPages // Garante que seja um inteiro
        ];
    }

      /**
     * Obtém TODOS os produtos cadastrados no sistema (para uso administrativo).
     * Este método não filtra por disponibilidade de estoque.
     * @return Product[]
     */
    public function getAllProducts(): array
    {
        return $this->productRepository->findAll();
    }

}