<?php

use App\Core\Router;
use App\Controller\HomeController;
use App\Controller\ProductController;
use App\Controller\ClientController;
use App\Controller\SellerController;
use App\Controller\AdminController;
use App\Controller\AuthController;
use App\Middleware\AuthMiddleware;
use App\Middleware\AdminMiddleware;
use App\Middleware\GuestMiddleware; // Exemplo para rotas de login/registro

/**
 * Define as rotas da aplicação.
 * @param Router $router O objeto Router para adicionar as rotas.
 */
function defineWebRoutes(Router $router): void
{
    // Middlewares
    $router->middleware('auth', AuthMiddleware::class);
    $router->middleware('admin', AdminMiddleware::class);
    // ... registre outros middlewares

    // Rotas públicas (não exigem login)
    $router->get('', [HomeController::class, 'index']); // Página inicial
    $router->get('products', [ProductController::class, 'index']); // Listar produtos (com paginação)
    $router->get('products/{id}', [ProductController::class, 'show']); // Detalhes de um produto

    // Rotas de Autenticação
    $router->get('login', [AuthController::class, 'showLoginForm'], ['guest']); // Exibe formulário de login
    $router->post('login', [AuthController::class, 'login']); // Processa login
    $router->get('register', [AuthController::class, 'showRegisterForm'], ['guest']); // Exibe formulário de registro
    $router->post('register', [AuthController::class, 'register']); // Processa registro
    $router->get('logout', [AuthController::class, 'logout'], ['auth']); // Logout (requer autenticação)

    // Rotas protegidas para Clientes (exigem login)
    $router->get('cart', [ClientController::class, 'viewCart'], ['auth', 'client']);
    $router->post('cart/add', [ClientController::class, 'addToCart'], ['auth', 'client']);
    $router->get('checkout', [ClientController::class, 'showCheckout'], ['auth', 'client']);
    $router->post('checkout', [ClientController::class, 'processCheckout'], ['auth', 'client']);
    $router->get('orders', [ClientController::class, 'viewOrders'], ['auth', 'client']);

    // Rotas protegidas para Vendedores (exigem login e role 'seller')
    $router->get('seller/dashboard', [SellerController::class, 'dashboard'], ['auth', 'seller']);
    $router->get('seller/products', [SellerController::class, 'listProducts'], ['auth', 'seller']);
    $router->get('seller/products/create', [SellerController::class, 'createProductForm'], ['auth', 'seller']);
    $router->post('seller/products', [SellerController::class, 'storeProduct'], ['auth', 'seller']);
    $router->get('seller/products/{id}/edit', [SellerController::class, 'editProductForm'], ['auth', 'seller']);
    $router->post('seller/products/{id}', [SellerController::class, 'updateProduct'], ['auth', 'seller']);
    $router->post('seller/products/{id}/delete', [SellerController::class, 'deleteProduct'], ['auth', 'seller']);

    // Rotas protegidas para Administradores (exigem login e role 'admin')
    $router->get('admin/dashboard', [AdminController::class, 'dashboard'], ['auth', 'admin']);
    $router->get('admin/users', [AdminController::class, 'listUsers'], ['auth', 'admin']);
    $router->get('admin/products', [AdminController::class, 'listAllProducts'], ['auth', 'admin']);
    // ... outras rotas de administração
}