<?php

namespace App\Controller;

use App\Core\Request;
use App\Core\Response;
use App\Core\Authenticator;
use App\Service\UserService;
use App\Service\ProductService;
use App\Service\OrderService;
use App\Service\ClientService; // Para gerenciar clientes
use App\Service\LogService;
use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use App\Repository\OrderRepository;
use App\Repository\ClientRepository;
use App\Repository\CartRepository; 
use App\Repository\LogRepository; 
use App\Repository\CouponRepository; 
use App\Config\Database;

class AdminController
{
    private UserService $userService;
    private ProductService $productService;
    private OrderService $orderService;
    private ClientService $clientService;
    private LogService $logService;

    // O construtor agora aceita todas as dependências necessárias
    public function __construct(
        UserService $userService,
        ProductService $productService,
        OrderService $orderService,
        ClientService $clientService,
        LogService $logService
    ) {
        $this->userService = $userService;
        $this->productService = $productService;
        $this->orderService = $orderService;
        $this->clientService = $clientService;
        $this->logService = $logService;
    }

    public function dashboard(Request $request): Response
    {
        $adminId = Authenticator::getUserId(); // Assume que o middleware já validou
        $this->logService->log('Admin', 'Accessed dashboard', $adminId);

        try {
            // Obtenha dados para o dashboard
            $totalUsers = count($this->userService->getAllUsers());
            $totalProducts = count($this->productService->getAllProducts());
            $totalOrders = count($this->orderService->getAllOrders());

            return Response::view('admin/dashboard', [
                'totalUsers' => $totalUsers,
                'totalProducts' => $totalProducts,
                'totalOrders' => $totalOrders,
                // Adicione mais métricas conforme necessário
            ]);
        } catch (\Exception $e) {
            $this->logService->log('Admin', 'Error accessing dashboard', $adminId, ['error' => $e->getMessage()]);
            return Response::view('errors/500', ['message' => 'Erro ao carregar o dashboard administrativo: ' . $e->getMessage()], 500);
        }
    }

    public function listUsers(Request $request): Response
    {
        $adminId = Authenticator::getUserId();
        $this->logService->log('Admin', 'Viewed user list', $adminId);

        try {
            $users = $this->userService->getAllUsers();
            return Response::view('admin/users/index', ['users' => $users]);
        } catch (\Exception $e) {
            $this->logService->log('Admin', 'Error listing users', $adminId, ['error' => $e->getMessage()]);
            return Response::view('errors/500', ['message' => 'Erro ao carregar a lista de usuários: ' . $e->getMessage()], 500);
        }
    }

    public function showUser(Request $request): Response
    {
        $adminId = Authenticator::getUserId();
        $userId = (int) $request->getRouteParam('id');

        if (!$userId) {
            return Response::view('errors/400', ['message' => 'ID do usuário não especificado.'], 400);
        }

        try {
            $user = $this->userService->getUserById($userId);
            if (!$user) {
                return Response::view('errors/404', ['message' => 'Usuário não encontrado.'], 404);
            }
            $this->logService->log('Admin', 'Viewed user details', $adminId, ['viewedUserId' => $userId]);
            return Response::view('admin/users/show', ['user' => $user]);
        } catch (\Exception $e) {
            $this->logService->log('Admin', 'Error viewing user details', $adminId, ['viewedUserId' => $userId, 'error' => $e->getMessage()]);
            return Response::view('errors/500', ['message' => 'Erro ao carregar detalhes do usuário: ' . $e->getMessage()], 500);
        }
    }

    public function updateUser(Request $request): Response
    {
        $adminId = Authenticator::getUserId();
        $userId = (int) $request->getRouteParam('id');

        if (!$userId) {
            return Response::json(['success' => false, 'message' => 'ID do usuário não especificado.'], 400);
        }

        $data = json_decode($request->getBody(), true); // Assumindo JSON
        // Crie um DTO de atualização.
        // Assumindo um UserUpdateDTO que pode ser usado para atualizar campos específicos.
        $userUpdateDTO = new \App\DTO\UserUpdateDTO([
            'id' => $userId,
            'email' => $data['email'] ?? null,
            'firstName' => $data['first_name'] ?? null,
            'lastName' => $data['last_name'] ?? null,
            'role' => $data['role'] ?? null,
            'password' => $data['password'] ?? null // Cuidado ao permitir atualização de senha aqui
        ]);

        try {
            $success = $this->userService->updateUser($userUpdateDTO);
            if ($success) {
                $this->logService->log('Admin', 'Updated user', $adminId, ['updatedUserId' => $userId]);
                return Response::json(['success' => true, 'message' => 'Usuário atualizado com sucesso!'], 200);
            } else {
                $this->logService->log('Admin', 'Failed to update user', $adminId, ['updatedUserId' => $userId, 'data' => $data]);
                return Response::json(['success' => false, 'message' => 'Falha ao atualizar o usuário.'], 500);
            }
        } catch (\InvalidArgumentException $e) {
            $this->logService->log('Admin', 'User update failed: Invalid data', $adminId, ['updatedUserId' => $userId, 'error' => $e->getMessage()]);
            return Response::json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->logService->log('Admin', 'User update error', $adminId, ['updatedUserId' => $userId, 'error' => $e->getMessage()]);
            return Response::json(['success' => false, 'message' => 'Erro ao atualizar usuário: ' . $e->getMessage()], 500);
        }
    }

    public function deleteUser(Request $request): Response
    {
        $adminId = Authenticator::getUserId();
        $userId = (int) $request->getRouteParam('id');

        if (!$userId) {
            return Response::json(['success' => false, 'message' => 'ID do usuário não especificado.'], 400);
        }

        // Impedir que o admin delete a si mesmo
        if ($userId === $adminId) {
            return Response::json(['success' => false, 'message' => 'Você não pode deletar sua própria conta de administrador.'], 403);
        }

        try {
            $success = $this->userService->deleteUser($userId); // Chamando deleteUser do UserService
            if ($success) {
                $this->logService->log('Admin', 'Deleted user', $adminId, ['deletedUserId' => $userId]);
                return Response::json(['success' => true, 'message' => 'Usuário deletado com sucesso!'], 200);
            } else {
                $this->logService->log('Admin', 'Failed to delete user', $adminId, ['deletedUserId' => $userId]);
                return Response::json(['success' => false, 'message' => 'Falha ao deletar o usuário.'], 500);
            }
        } catch (\Exception $e) {
            $this->logService->log('Admin', 'User deletion error', $adminId, ['deletedUserId' => $userId, 'error' => $e->getMessage()]);
            return Response::json(['success' => false, 'message' => 'Erro ao deletar usuário: ' . $e->getMessage()], 500);
        }
    }

    public function listAllProducts(Request $request): Response
    {
        $adminId = Authenticator::getUserId();
        $this->logService->log('Admin', 'Viewed all products list', $adminId);

        try {
            $products = $this->productService->getAllProducts(); 
            return Response::view('admin/products/index', ['products' => $products]);
        } catch (\Exception $e) {
            $this->logService->log('Admin', 'Error listing all products', $adminId, ['error' => $e->getMessage()]);
            return Response::view('errors/500', ['message' => 'Erro ao carregar a lista de todos os produtos: ' . $e->getMessage()], 500);
        }
    }

    public function manageOrders(Request $request): Response
    {
        $adminId = Authenticator::getUserId();
        $this->logService->log('Admin', 'Viewed all orders list', $adminId);

        try {
            $orders = $this->orderService->getAllOrders();
            return Response::view('admin/orders/index', ['orders' => $orders]);
        } catch (\Exception $e) {
            $this->logService->log('Admin', 'Error listing all orders', $adminId, ['error' => $e->getMessage()]);
            return Response::view('errors/500', ['message' => 'Erro ao carregar a lista de todos os pedidos: ' . $e->getMessage()], 500);
        }
    }

    public function updateOrderStatus(Request $request): Response
    {
        $adminId = Authenticator::getUserId();
        $orderId = (int) $request->getRouteParam('id'); // Assumindo /admin/orders/{id}/status
        $data = json_decode($request->getBody(), true);
        $newStatus = $data['status'] ?? null;

        if (!$orderId || empty($newStatus)) {
            return Response::json(['success' => false, 'message' => 'Dados inválidos.'], 400);
        }

        try {
            $success = $this->orderService->updateOrderStatus($orderId, $newStatus);
            if ($success) {
                $this->logService->log('Admin', 'Updated order status', $adminId, ['orderId' => $orderId, 'newStatus' => $newStatus]);
                return Response::json(['success' => true, 'message' => 'Status do pedido atualizado com sucesso!'], 200);
            } else {
                $this->logService->log('Admin', 'Failed to update order status', $adminId, ['orderId' => $orderId, 'newStatus' => $newStatus]);
                return Response::json(['success' => false, 'message' => 'Falha ao atualizar o status do pedido.'], 500);
            }
        } catch (\Exception $e) {
            $this->logService->log('Admin', 'Order status update error', $adminId, ['orderId' => $orderId, 'newStatus' => $newStatus, 'error' => $e->getMessage()]);
            return Response::json(['success' => false, 'message' => 'Erro ao atualizar status do pedido: ' . $e->getMessage()], 500);
        }
    }

    public function viewLogs(Request $request): Response
    {
        $adminId = Authenticator::getUserId();
        $this->logService->log('Admin', 'Viewed system logs', $adminId);

        try {
            $logs = $this->logService->getAllLogs();
            return Response::view('admin/logs/index', ['logs' => $logs]);
        } catch (\Exception $e) {
            $this->logService->log('Admin', 'Error viewing system logs', $adminId, ['error' => $e->getMessage()]);
            return Response::view('errors/500', ['message' => 'Erro ao carregar os logs do sistema: ' . $e->getMessage()], 500);
        }
    }
}
