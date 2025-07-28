<?php

namespace App\Model;

use DateTime;
use App\Model\Product; 

/**
 * Class Seller
 * @package App\Model
 *
 * Representa um usuário com perfil de vendedor.
 * Herda de User e adiciona funcionalidades específicas de gerenciamento de produtos e vendas.
 */
class Seller extends User
{
    /**
     * Construtor da classe Seller.
     * Define o papel como 'seller' por padrão.
     *
     * @param string $email O email do vendedor.
     * @param string $firstName O primeiro nome do vendedor.
     * @param string $lastName O sobrenome do vendedor.
     * @param string $pswd A senha (já criptografada) do vendedor.
     * @param int|null $id O ID do vendedor (opcional).
     * @param DateTime|null $createdAt A data de criação (opcional).
     */
    public function __construct(
        string $email,
        string $firstName,
        string $lastName,
        string $pswd,
        ?int $id = null,
        ?DateTime $createdAt = null
    ) {
        parent::__construct($email, $firstName, $lastName, 'seller', $pswd, $id, $createdAt);
    }

    /**
     * Obtém todos os produtos cadastrados por este vendedor.
     * @return array<Product> Um array de objetos Product.
     */
    public function getMyProducts(): array
    {
        // Lógica para buscar produtos associados a este seller (via ProductRepository)
        // Regra: Usuários só podem ver os clientes que compraram os produtos que eles cadastraram. [cite: 14]
        echo "Seller " . $this->getFirstName() . ": Obtendo meus produtos...\n";
        return []; // Retorno de exemplo
    }

    /**
     * Adiciona um novo produto.
     * [cite_start]Regra: Só podem criar produtos via formulários HTML padrão com method="POST". [cite: 15]
     * @param array $productData Os dados do produto a ser adicionado.
     * @return Product O objeto Product criado.
     */
    public function addProduct(array $productData): Product
    {
        // Lógica para criar e persistir um novo produto (via ProductService/ProductRepository)
        // Associar o produto ao seller atual ($this->getId())
        echo "Seller " . $this->getFirstName() . ": Adicionando novo produto: " . $productData['name'] . "\n";
        return new Product(
            0, // ID temporário, seria gerado pelo DB
            $productData['name'],
            $productData['price'],
            $productData['category'],
            $productData['description'],
            $productData['imageUrl'],
            $productData['stock'],
            $this->getId() // Associar ao seller logado
        );
    }

    /**
     * Atualiza os dados de um produto.
     * [cite_start]Regra: Não podem editar produtos de outros usuários. [cite: 16]
     * @param int $productId O ID do produto a ser atualizado.
     * @param array $data Os dados a serem atualizados.
     * @return bool
     */
    public function updateProduct(int $productId, array $data): bool
    {
        // É CRÍTICO verificar se o produto pertence a este vendedor antes de atualizar.
   
        echo "Seller " . $this->getFirstName() . ": Atualizando produto ID " . $productId . "...\n";
        return true; // Supondo sucesso e permissão
    }

    /**
     * Deleta um produto.
     * [cite_start]Regra: Não podem excluir produtos de outros usuários. [cite: 16]
     * @param int $productId O ID do produto a ser deletado.
     * @return bool
     */
    public function deleteProduct(int $productId): bool
    {
        // Lógica para deletar um produto.
        // É CRÍTICO verificar se o produto pertence a este vendedor antes de deletar.
        echo "Seller " . $this->getFirstName() . ": Deletando produto ID " . $productId . "...\n";
        return true; // Supondo sucesso e permissão
    }

    /**
     * Obtém o estoque atual de um produto.
     * @param int $productId O ID do produto.
     * @return int
     */
    public function getProductStock(int $productId): int
    {
        // Lógica para buscar o estoque de um produto (via ProductRepository)
        echo "Seller " . $this->getFirstName() . ": Verificando estoque do produto ID " . $productId . "...\n";
        return 10; // Retorno de exemplo
    }

    /**
     * Aplica um desconto a um produto.
     * @param int $productId O ID do produto.
     * @param float $discount O valor do desconto (ex: 0.10 para 10%).
     * @return bool
     */
    public function applyDiscount(int $productId, float $discount): bool
    {
        // Lógica para aplicar desconto a um produto (via ProductService/ProductRepository)
        // É CRÍTICO verificar se o produto pertence a este vendedor.
        echo "Seller " . $this->getFirstName() . ": Aplicando " . ($discount * 100) . "% de desconto ao produto ID " . $productId . "...\n";
        return true; // Supondo sucesso e permissão
    }

    /**
     * Obtém todas as vendas (ordens) associadas aos produtos deste vendedor.
     * @return array<Order> Um array de objetos Order.
     */
    public function getMySales(): array
    {
        // Lógica para buscar ordens onde os produtos são deste vendedor (via OrderRepository/LogRepository)
        // Regra: Usuários só podem ver os clientes que compraram os produtos que eles cadastraram. [cite: 14]
        echo "Seller " . $this->getFirstName() . ": Obtendo minhas vendas...\n";
        return []; // Retorno de exemplo
    }

    /**
     * Obtém os logs específicos deste vendedor (ações relacionadas a produtos, etc.).
     * @return array<Log> Um array de objetos Log.
     */
    public function getSellerLogs(): array
    {
        // Lógica para buscar logs associados a este vendedor (via LogRepository)
        echo "Seller " . $this->getFirstName() . ": Obtendo meus logs...\n";
        return []; // Retorno de exemplo
    }
}