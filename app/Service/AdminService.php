<?php

namespace App\Service;

use App\Repository\UserRepositoryInterface;
use App\Repository\ProductRepositoryInterface;
use App\Repository\OrderRepositoryInterface;
use App\Repository\LogRepositoryInterface;
use App\DTO\UserDTO; 
use App\DTO\ProductUpdateDTO;
use App\DTO\ProductCreateDTO;
use App\Model\User;
use App\Model\Product;
use App\Model\Order;
use App\Model\Log;
use Exception;

/**
 * Class AdminService
 * @package App\Service
 *
 * Responsável pelas operações administrativas do sistema,
 * com acesso total e gerenciamento de usuários, produtos, pedidos e logs.
 */
class AdminService
{
    private UserRepositoryInterface $userRepository;
    private ProductRepositoryInterface $productRepository;
    private OrderRepositoryInterface $orderRepository;
    private LogRepositoryInterface $logRepository;
    private UserService $userService; // Para operações de criação/edição/deleção de usuário com lógica de negócio
    private ProductService $productService; // Para operações de produto com lógica de negócio
    private OrderService $orderService; // Para operações de pedido com lógica de negócio
    private LogService $logService; // Para registrar logs administrativos

    public function __construct(
        UserRepositoryInterface $userRepository,
        ProductRepositoryInterface $productRepository,
        OrderRepositoryInterface $orderRepository,
        LogRepositoryInterface $logRepository,
        UserService $userService,
        ProductService $productService,
        OrderService $orderService,
        LogService $logService
    ) {
        $this->userRepository = $userRepository;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->logRepository = $logRepository;
        $this->userService = $userService;
        $this->productService = $productService;
        $this->orderService = $orderService;
        $this->logService = $logService;
    }

    /**
     * Retorna todos os usuários registrados no sistema.
     * @return User[] Um array de objetos User.
     */
    public function getAllUsers(): array
    {
        $this->logService->log('INFO', 'Admin acessou todos os usuários.', $_SESSION['user_id'] ?? null);
        return $this->userRepository->findAll();
    }

    /**
     * Retorna todos os produtos registrados no sistema.
     * @return Product[] Um array de objetos Product.
     */
    public function getAllProducts(): array
    {
        $this->logService->log('INFO', 'Admin acessou todos os produtos.', $_SESSION['user_id'] ?? null);
        return $this->productRepository->findAll();
    }

    /**
     * Retorna todos os pedidos registrados no sistema.
     * @return Order[] Um array de objetos Order.
     */
    public function getAllOrders(): array
    {
        $this->logService->log('INFO', 'Admin acessou todos os pedidos.', $_SESSION['user_id'] ?? null);
        return $this->orderRepository->findAll();
    }

    /**
     * Retorna todos os logs do sistema.
     * @return Log[] Um array de objetos Log.
     */
    public function getAllLogs(): array
    {
        $this->logService->log('INFO', 'Admin acessou todos os logs do sistema.', $_SESSION['user_id'] ?? null);
        return $this->logRepository->findAll();
    }

    /**
     * Retorna os logs de um usuário específico.
     * @param int $userId O ID do usuário para filtrar os logs.
     * @return Log[] Um array de objetos Log.
     */
    public function getLogOfUser(int $userId): array
    {
        $this->logService->log('INFO', "Admin acessou logs do usuário ID: {$userId}.", $_SESSION['user_id'] ?? null, ['target_user_id' => $userId]);
        return $this->logRepository->findByUserId($userId);
    }

    /**
     * Gerencia um usuário (atualiza ou deleta).
     * @param int $userId O ID do usuário a ser gerenciado.
     * @param string $action A ação a ser realizada ('update', 'delete').
     * @param array $data Dados para a atualização (apenas para 'update').
     * @return bool True se a operação for bem-sucedida, false caso contrário.
     * @throws Exception Se a ação for inválida ou a operação falhar.
     */
    public function manageUser(int $userId, string $action, array $data = []): bool
    {
        $currentUser = $_SESSION['user_id'] ?? null; // ID do admin logado

        $this->logService->log('INFO', "Admin tentando gerenciar usuário ID: {$userId} - Ação: {$action}.", $currentUser, ['target_user_id' => $userId, 'action_type' => $action, 'data' => $data]);

        switch ($action) {
            case 'update':
                $user = $this->userRepository->findById($userId);
                if (!$user) {
                    throw new Exception("Usuário ID {$userId} não encontrado para atualização.");
                }

                // Admins podem editar qualquer usuário
                // Criar um DTO específico para atualização de admin se necessário
                $userDTO = new UserDTO($data);
                $userDTO->id = $userId; // Garante que o ID do DTO corresponda ao ID do usuário sendo editado


                // Atualizar dados gerais do usuário
                if (isset($data['email'])) $user->setEmail($data['email']);
                if (isset($data['firstName'])) $user->setFirstName($data['firstName']);
                if (isset($data['lastName'])) $user->setLastName($data['lastName']);
                if (isset($data['newPassword']) && !empty($data['newPassword'])) {
                    $hashedPassword = password_hash($data['newPassword'], PASSWORD_BCRYPT);
                    if ($hashedPassword === false) {
                        throw new Exception("Falha ao criptografar nova senha para usuário ID {$userId}.");
                    }
                    $user->setPswd($hashedPassword);
                }

                $result = $this->userRepository->update($user);
                if ($result) {
                    $this->logService->log('SUCCESS', "Admin atualizou usuário ID: {$userId}.", $currentUser, ['target_user_id' => $userId, 'updated_data' => $data]);
                } else {
                    $this->logService->log('ERROR', "Admin falhou ao atualizar usuário ID: {$userId}.", $currentUser, ['target_user_id' => $userId, 'updated_data' => $data]);
                }
                return $result;

            case 'delete':
                // Cuidado: Excluir um usuário admin pode ser perigoso.
                // E um admin não deve conseguir excluir a si mesmo.
                if ($userId === $currentUser) {
                    throw new Exception("Um administrador não pode excluir sua própria conta através do gerenciamento.");
                }
                $result = $this->userRepository->delete($userId);
                if ($result) {
                    $this->logService->log('SUCCESS', "Admin deletou usuário ID: {$userId}.", $currentUser, ['target_user_id' => $userId]);
                } else {
                    $this->logService->log('ERROR', "Admin falhou ao deletar usuário ID: {$userId}.", $currentUser, ['target_user_id' => $userId]);
                }
                return $result;

            default:
                throw new \InvalidArgumentException("Ação de usuário inválida para o admin: {$action}.");
        }
    }

     /**
     * Gerencia ações em produtos (adicionar, atualizar, deletar).
     * @param int $productId O ID do produto a ser gerenciado (pode ser 0 para 'add').
     * @param string $action A ação a ser realizada ('add', 'update', 'delete').
     * @param array $data Dados adicionais para a ação (ex: ProductCreateDTO data, ProductUpdateDTO data).
     * @return bool True se a operação for bem-sucedida.
     * @throws Exception Se o usuário não for admin, ação inválida ou falha na operação.
     */
    public function manageProduct(int $productId, string $action, array $data = []): bool
    {
        // Esta lógica de autorização deve ser robusta em um ambiente de produção
        $currentUserRole = $_SESSION['user_role'] ?? null;
        if ($currentUserRole !== 'admin') {
            throw new Exception("Apenas administradores podem gerenciar produtos.");
        }

        switch ($action) {
            case 'add':
                // 'add' não precisa de productId. $data deve conter todos os campos para ProductCreateDTO.
                // Aqui, o sellerId precisa ser fornecido nos $data, ou ser default do admin, ou ter um DTO específico.
                // Para simplificar, assumimos que $data inclui 'sellerId'.
                if (!isset($data['sellerId'])) {
                    throw new Exception("Para adicionar um produto, o 'sellerId' é obrigatório nos dados.");
                }
                $productCreateDTO = new ProductCreateDTO(
                    $data['name'],
                    $data['description'],
                    $data['price'],
                    $data['category'],
                    $data['imageUrl'] ?? null,
                    $data['stock'] ?? 0,
                    $data['sellerId']
                );
                $this->productService->createProduct($productCreateDTO);
                return true;
            case 'update':
                if ($productId === 0) {
                    throw new Exception("ID do produto é obrigatório para atualização.");
                }
                
                $productUpdateDTO = ProductUpdateDTO::fromArray($data);
                return $this->productService->updateProduct($productId, $productUpdateDTO);
            case 'delete':
                if ($productId === 0) {
                    throw new Exception("ID do produto é obrigatório para exclusão.");
                }
                return $this->productService->deleteProduct($productId);
            default:
                throw new Exception("Ação inválida para gerenciar produto.");
        }
    }

    /**
     * Gerencia um pedido (muda status, cancela, etc.).
     * @param int $orderId O ID do pedido a ser gerenciado.
     * @param string $action A ação a ser realizada ('update_status', 'cancel').
     * @param array $data Dados para a atualização (ex: ['status' => 'COMPLETED']).
     * @return bool True se a operação for bem-sucedida, false caso contrário.
     * @throws Exception Se a ação for inválida ou a operação falhar.
     */
    public function manageOrder(int $orderId, string $action, array $data = []): bool
    {
        $currentUser = $_SESSION['user_id'] ?? null;

        $this->logService->log('INFO', "Admin tentando gerenciar pedido ID: {$orderId} - Ação: {$action}.", $currentUser, ['target_order_id' => $orderId, 'action_type' => $action, 'data' => $data]);

        switch ($action) {
            case 'update_status':
                if (!isset($data['status'])) {
                    throw new \InvalidArgumentException("Para atualizar o status do pedido, o campo 'status' é obrigatório.");
                }
                $newStatus = $data['status'];
                // Adicione validação para os status permitidos se necessário
                $result = $this->orderService->updateOrderStatus($orderId, $newStatus);
                if ($result) {
                    $this->logService->log('SUCCESS', "Admin atualizou status do pedido ID: {$orderId} para {$newStatus}.", $currentUser, ['target_order_id' => $orderId, 'new_status' => $newStatus]);
                } else {
                    $this->logService->log('ERROR', "Admin falhou ao atualizar status do pedido ID: {$orderId}.", $currentUser, ['target_order_id' => $orderId, 'new_status' => $newStatus]);
                }
                return $result;

            case 'cancel':
                $result = $this->orderService->cancelOrder($orderId);
                 if ($result) {
                    $this->logService->log('SUCCESS', "Admin cancelou pedido ID: {$orderId}.", $currentUser, ['target_order_id' => $orderId]);
                } else {
                    $this->logService->log('ERROR', "Admin falhou ao cancelar pedido ID: {$orderId}.", $currentUser, ['target_order_id' => $orderId]);
                }
                return $result;

            default:
                throw new \InvalidArgumentException("Ação de pedido inválida para o admin: {$action}.");
        }
    }
}