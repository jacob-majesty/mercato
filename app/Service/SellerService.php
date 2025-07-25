<?php

namespace App\Service;

use App\DTO\SellerCreateDTO;
use App\Model\Seller;
use App\Repository\SellerRepositoryInterface;
use App\Repository\ProductRepositoryInterface; 
use App\DTO\ProductCreateDTO;
use App\DTO\ProductUpdateDTO;
use App\Service\ProductService;
use Exception;

/**
 * Class SellerService
 * @package App\Service
 *
 * Gerencia a lógica de negócio para operações de vendedores.
 */
class SellerService
{
    private SellerRepositoryInterface $sellerRepository;
    private ProductService $productService; // Necessário para gerenciar produtos de um vendedor
    // private LogService $logService; // Futura dependência para logs do vendedor

    public function __construct(
        SellerRepositoryInterface $sellerRepository,
        ProductService $productService
        // LogService $logService // Adicionar quando LogService for implementado
    ) {
        $this->sellerRepository = $sellerRepository;
        $this->productService = $productService;
        // $this->logService = $logService;
    }

    /**
     * Registra um novo vendedor no sistema.
     * @param SellerCreateDTO $sellerDTO Dados para criação do vendedor.
     * @return Seller O objeto Seller recém-criado.
     * @throws Exception Se o email já estiver em uso.
     */
    public function registerSeller(SellerCreateDTO $sellerDTO): Seller
    {
        if ($this->sellerRepository->findByEmail($sellerDTO->email)) {
            throw new Exception("Email já cadastrado para outro vendedor.");
        }

        $seller = new Seller(
            $sellerDTO->email,
            $sellerDTO->firstName,
            $sellerDTO->lastName,
            $sellerDTO->password
        );

        return $this->sellerRepository->save($seller);
    }

    /**
     * Busca um vendedor pelo seu ID.
     * @param int $id O ID do vendedor.
     * @return Seller|null
     */
    public function getSellerById(int $id): ?Seller
    {
        return $this->sellerRepository->findById($id);
    }

    /**
     * Atualiza o perfil de um vendedor.
     * @param int $sellerId O ID do vendedor a ser atualizado.
     * @param SellerCreateDTO $sellerDTO Objeto DTO com os dados a serem atualizados.
     * @return Seller O objeto Seller atualizado.
     * @throws Exception Se o vendedor não for encontrado ou o email já estiver em uso por outro vendedor.
     */
    public function updateSellerProfile(int $sellerId, SellerCreateDTO $sellerDTO): Seller
    {
        $seller = $this->sellerRepository->findById($sellerId);
        if (!$seller) {
            throw new Exception("Vendedor não encontrado.");
        }

        $existingSellerWithEmail = $this->sellerRepository->findByEmail($sellerDTO->email);
        if ($existingSellerWithEmail && $existingSellerWithEmail->getId() !== $sellerId) {
            throw new Exception("Email já cadastrado para outro vendedor.");
        }

        $seller->setEmail($sellerDTO->email);
        $seller->setFirstName($sellerDTO->firstName);
        $seller->setLastName($sellerDTO->lastName);
        if (!empty($sellerDTO->password)) {
            $seller->setPswd($sellerDTO->password);
        }

        if (!$this->sellerRepository->update($seller)) {
            throw new Exception("Falha ao atualizar o perfil do vendedor.");
        }

        return $seller;
    }

    /**
     * Deleta um vendedor do sistema.
     * @param int $sellerId O ID do vendedor a ser deletado.
     * @return bool True se a exclusão for bem-sucedida.
     * @throws Exception Se o vendedor não for encontrado.
     */
    public function deleteSeller(int $sellerId): bool
    {
        $seller = $this->sellerRepository->findById($sellerId);
        if (!$seller) {
            throw new Exception("Vendedor não encontrado.");
        }
        return $this->sellerRepository->delete($sellerId);
    }

    /**
     * Obtém todos os vendedores cadastrados.
     * @return Seller[]
     */
    public function getAllSellers(): array
    {
        return $this->sellerRepository->findAll();
    }

    /**
     * Adiciona um novo produto para um vendedor específico.
     * @param int $sellerId O ID do vendedor.
     * @param array $productData Dados do produto (name, price, etc.).
     * @return \App\Model\Product O produto criado.
     * @throws Exception Se o vendedor não for encontrado ou falha na criação.
     */
    public function addProduct(int $sellerId, array $productData): \App\Model\Product
    {
        $seller = $this->getSellerById($sellerId);
        if (!$seller) {
            throw new Exception("Vendedor não encontrado para adicionar produto.");
        }
        $productDTO = new ProductCreateDTO(
            $productData['name'],
            $productData['description'],
            $productData['price'],
            $productData['category'],
            $productData['imageUrl'] ?? null,
            $productData['stock'] ?? 0,
            $sellerId
        );
        return $this->productService->createProduct($productDTO);
    }

    /**
     * Atualiza um produto de um vendedor, verificando a propriedade.
     * @param int $sellerId O ID do vendedor.
     * @param int $productId O ID do produto a ser atualizado.
     * @param array $productData Dados para atualização.
     * @return bool True se atualizado, false caso contrário.
     * @throws Exception Se o produto não pertencer ao vendedor ou não for encontrado.
     */
    public function updateProduct(int $sellerId, int $productId, array $productData): bool
    {
        $product = $this->productService->getProductById($productId);
        if (!$product) {
            throw new Exception("Produto não encontrado.");
        }
        if ($product->getSellerId() !== $sellerId) {
            throw new Exception("Este produto não pertence ao vendedor.");
        }
        $productUpdateDTO = ProductUpdateDTO::fromArray($productData);
        return $this->productService->updateProduct($productId, $productUpdateDTO);
    }

    /**
     * Deleta um produto de um vendedor, verificando a propriedade.
     * @param int $sellerId O ID do vendedor.
     * @param int $productId O ID do produto a ser deletado.
     * @return bool True se deletado, false caso contrário.
     * @throws Exception Se o produto não pertencer ao vendedor ou não for encontrado.
     */
    public function deleteProduct(int $sellerId, int $productId): bool
    {
        $product = $this->productService->getProductById($productId);
        if (!$product) {
            throw new Exception("Produto não encontrado para deletar.");
        }
        if ($product->getSellerId() !== $sellerId) {
            throw new Exception("Este produto não pertence ao vendedor.");
        }
        return $this->productService->deleteProduct($productId);
    }

    /**
     * Obtém todos os produtos cadastrados por este vendedor.
     * @param int $sellerId O ID do vendedor.
     * @return \App\Model\Product[]
     */
    public function getMyProducts(int $sellerId): array
    {
        return $this->productService->getProductsBySellerId($sellerId);
    }

    /**
     * Obtém o estoque atual de um produto.
     * @param int $productId O ID do produto.
     * @return int O estoque atual do produto.
     * @throws Exception Se o produto não for encontrado.
     */
    public function getProductStock(int $productId): int
    {
        $product = $this->productService->getProductById($productId);
        if (!$product) {
            throw new Exception("Produto não encontrado.");
        }
        return $product->getStock(); // Retorna o estoque do objeto Product
    }

    /**
     * Obtém as vendas (pedidos) associadas aos produtos deste vendedor.
     * @param int $sellerId O ID do vendedor.
     * @return array<\App\Model\Order>
     */
    public function getMySales(int $sellerId): array
    {
        echo "Funcionalidade 'getMySales' para Seller ID {$sellerId} ainda não implementada completamente.\n";
        return [];
    }

    /**
     * Aplica um desconto a um produto de um vendedor, verificando a propriedade.
     * @param int $sellerId O ID do vendedor.
     * @param int $productId O ID do produto.
     * @param float $discount O valor do desconto (ex: 0.10 para 10%).
     * @return bool
     * @throws Exception Se o produto não pertencer ao vendedor ou não for encontrado.
     */
    public function applyDiscount(int $sellerId, int $productId, float $discount): bool
    {
        $product = $this->productService->getProductById($productId);
        if (!$product) {
            throw new Exception("Produto não encontrado para aplicar desconto.");
        }
        if ($product->getSellerId() !== $sellerId) {
            throw new Exception("Este produto não pertence ao vendedor.");
        }
        return $this->productService->applyDiscountToProduct($productId, $discount);
    }

    /**
     * Obtém os logs específicos deste vendedor (ações relacionadas a produtos, etc.).
     * Este método dependerá da implementação de um LogService/LogRepository.
     * @param int $sellerId O ID do vendedor.
     * @return array<\App\Model\Log> Um array de objetos Log.
     */
    public function getSellerLogs(int $sellerId): array
    {
        // Lógica para buscar logs associados a este vendedor.
        // Necessitaria de uma dependência de LogService ou LogRepository.
        echo "Funcionalidade 'getSellerLogs' para Seller ID {$sellerId} ainda não implementada (requer LogService/Repository).\n";
        return [];
    }
}