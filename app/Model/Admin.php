<?php

namespace App\Model;

use DateTime;

/**
 * Class Admin
 * @package App\Model
 *
 * Representa um usuário com perfil de administrador.
 * Herda de User e adiciona funcionalidades específicas de administração.
 */
class Admin extends User
{
    /**
     * Construtor da classe Admin.
     * Define o papel como 'admin' por padrão.
     *
     * @param string $email O email do administrador.
     * @param string $firstName O primeiro nome do administrador.
     * @param string $lastName O sobrenome do administrador.
     * @param string $pswd A senha (já criptografada) do administrador.
     * @param int|null $id O ID do administrador (opcional).
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
        parent::__construct($email, $firstName, $lastName, 'admin', $pswd, $id, $createdAt);
    }

    /**
     * Obtém todos os usuários do sistema.
     * @return array<User> Um array de objetos User.
     */
    public function getAllUsers(): array
    {
        // Lógica para buscar todos os usuários do banco de dados (via UserRepository)
        // Exemplo: return (new UserRepository())->findAll();
        echo "Admin: Obtendo todos os usuários...\n";
        return []; // Retorno de exemplo
    }

    /**
     * Obtém todos os produtos do sistema.
     * @return array<Product> Um array de objetos Product.
     */
    public function getAllProducts(): array
    {
        // Lógica para buscar todos os produtos do banco de dados (via ProductRepository)
        echo "Admin: Obtendo todos os produtos...\n";
        return []; // Retorno de exemplo
    }

    /**
     * Obtém todas as ordens (compras) do sistema.
     * @return array<Order> Um array de objetos Order.
     */
    public function getAllOrders(): array
    {
        // Lógica para buscar todas as ordens do banco de dados (via OrderRepository)
        echo "Admin: Obtendo todas as ordens...\n";
        return []; // Retorno de exemplo
    }

    /**
     * Obtém todos os logs do sistema.
     * @return array<Log> Um array de objetos Log.
     */
    public function getAllLogs(): array
    {
        // Lógica para buscar todos os logs do banco de dados (via LogRepository)
        echo "Admin: Obtendo todos os logs...\n";
        return []; // Retorno de exemplo
    }

    /**
     * Obtém os logs de um usuário específico.
     * @param int $userId O ID do usuário.
     * @return array<Log> Um array de objetos Log associados ao usuário.
     */
    public function getLogOfUser(int $userId): array
    {
        // Lógica para buscar logs de um usuário específico (via LogRepository)
        echo "Admin: Obtendo logs para o usuário ID: " . $userId . "\n";
        return []; // Retorno de exemplo
    }

    /**
     * Gerencia (edita/deleta) um usuário.
     * @param int $userId O ID do usuário a ser gerenciado.
     * @param string $action A ação a ser realizada (ex: 'delete', 'update').
     * @param array $data Dados opcionais para a ação (ex: ['email' => 'novo@email.com']).
     * @return bool
     */
    public function manageUser(int $userId, string $action, array $data = []): bool
    {
        // Lógica para gerenciar um usuário (ex: deletar via UserRepository, ou atualizar)
        echo "Admin: Gerenciando usuário ID " . $userId . " com ação: " . $action . "\n";
        if ($action === 'delete') {
            // (new UserRepository())->delete($userId);
        } elseif ($action === 'update') {
            // (new UserRepository())->update($userId, $data);
        }
        return true; // Supondo sucesso
    }

    /**
     * Gerencia (edita/deleta) um produto.
     * @param int $productId O ID do produto a ser gerenciado.
     * @param string $action A ação a ser realizada (ex: 'delete', 'update').
     * @param array $data Dados opcionais para a ação.
     * @return bool
     */
    public function manageProduct(int $productId, string $action, array $data = []): bool
    {
        // Lógica para gerenciar um produto (ex: deletar via ProductRepository, ou atualizar)
        echo "Admin: Gerenciando produto ID " . $productId . " com ação: " . $action . "\n";
        if ($action === 'delete') {
            // (new ProductRepository())->delete($productId);
        } elseif ($action === 'update') {
            // (new ProductRepository())->update($productId, $data);
        }
        return true; // Supondo sucesso
    }

    /**
     * Gerencia (edita/cancela) uma ordem.
     * @param int $orderId O ID da ordem a ser gerenciada.
     * @param string $action A ação a ser realizada (ex: 'cancel', 'update_status').
     * @param array $data Dados opcionais para a ação.
     * @return bool
     */
    public function manageOrder(int $orderId, string $action, array $data = []): bool
    {
        // Lógica para gerenciar uma ordem (ex: cancelar via OrderService, ou atualizar status)
        echo "Admin: Gerenciando ordem ID " . $orderId . " com ação: " . $action . "\n";
        if ($action === 'cancel') {
            // (new OrderService())->cancelOrder($orderId);
        } elseif ($action === 'update_status') {
            // (new OrderService())->updateStatus($orderId, $data['newStatus']);
        }
        return true; // Supondo sucesso
    }
}