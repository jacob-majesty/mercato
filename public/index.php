<?php
// public/index.php

// --------------------------------------------------------------------
// ATENÇÃO: ESTE ARQUIVO DEVE COMEÇAR COM A TAG PHP E NADA MAIS.
// NENHUM ESPAÇO EM BRANCO, NENHUMA NOVA LINHA ANTES DESTA TAG.
// --------------------------------------------------------------------

// Inicia o buffer de saída. Isso deve ser a PRIMEIRA coisa a acontecer
// para capturar qualquer saída indesejada (incluindo warnings) antes
// que os cabeçalhos HTTP sejam enviados.
ob_start();

// Define o nível de relatório de erros.
// error_reporting(E_ALL); // Para desenvolvimento: mostra todos os erros
// ini_set('display_errors', 1); // Para desenvolvimento: exibe erros no navegador
// Para evitar que warnings apareçam no output e causem "headers already sent":
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING); // Ignora Notices e Warnings por enquanto

// Inclui o autoloader do Composer.
require_once __DIR__ . '/../vendor/autoload.php';

// Inicia o sistema de sessões globalmente no início do script.
use App\Core\Authenticator;
Authenticator::startSession();

// Importa as classes essenciais do Core.
use App\Config\Database;
use App\Core\Router;
use App\Core\Request;
use App\Core\Response;
// Não é necessário 'use Throwable;' se você estiver em PHP 7+ e usar \Throwable
// use Throwable; // Removido para evitar a warning "non-compound name" se for o caso

// Importa os Controladores.
use App\Controller\HomeController;
use App\Controller\ProductController;
use App\Controller\AuthController;
use App\Controller\ClientController;
use App\Controller\SellerController;
use App\Controller\AdminController;

// Importa os Middlewares.
use App\Middleware\AuthMiddleware;
use App\Middleware\AdminMiddleware;
use App\Middleware\SellerMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\ClientMiddleware; // Importa o ClientMiddleware

// Importa os Repositórios que serão instanciados diretamente.
use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use App\Repository\OrderRepository;
use App\Repository\ClientRepository;
use App\Repository\CartRepository;
use App\Repository\LogRepository;
use App\Repository\CouponRepository;
use App\Repository\AddressRepository; // Importa o AddressRepository

// Importa os Serviços que serão instanciados diretamente.
use App\Service\LogService;
use App\Service\UserService;
use App\Service\ProductService;
use App\Service\OrderService;
use App\Service\CartService;
use App\Service\ClientService;

// --------------------------------------------------------------------
// Configuração de Dependências (Injeção Manual para este exemplo)
// Em um projeto maior, use um Contêiner de Injeção de Dependência (DIC)
// --------------------------------------------------------------------

// Conexão com o banco de dados
$pdo = null;
try {
    $pdo = Database::getConnection();
} catch (\Throwable $e) { // Usar \Throwable para garantir captura de Errors e Exceptions
    error_log("Erro fatal ao conectar ao banco de dados: " . $e->getMessage());
    ob_clean(); // Limpa o buffer antes de enviar a resposta de erro
    Response::view('errors/500', ['message' => 'Erro interno do servidor: Falha na conexão com o banco de dados.'], 500)->send();
    exit();
}


// Repositórios
$userRepository = null;
$productRepository = null;
$clientRepository = null;
$cartRepository = null;
$logRepository = null;
$couponRepository = null;
$addressRepository = null;
$orderRepository = null; // Inicializa como null para garantir que a variável exista

try {
    $userRepository = new UserRepository($pdo);
    $productRepository = new ProductRepository($pdo);
    $clientRepository = new ClientRepository($pdo);
    $cartRepository = new CartRepository($pdo);
    $logRepository = new LogRepository($pdo);
    $couponRepository = new CouponRepository($pdo);

    // Instancia AddressRepository ANTES de OrderRepository
    if (!class_exists(AddressRepository::class)) {
        throw new Exception("Classe AddressRepository não encontrada.");
    }
    $addressRepository = new AddressRepository($pdo); // Instancia AddressRepository

    // DIAGNÓSTICO: Loga o tipo e valor de $addressRepository antes de instanciar OrderRepository
    error_log("DIAGNÓSTICO: \$addressRepository antes de OrderRepository: " . (is_object($addressRepository) ? get_class($addressRepository) : gettype($addressRepository)));

    // Instancia OrderRepository com AddressRepository
    if (!class_exists(OrderRepository::class)) {
        throw new Exception("Classe OrderRepository não encontrada.");
    }
    $orderRepository = new OrderRepository($pdo, $addressRepository);

} catch (\Throwable $e) { // Usar \Throwable para garantir captura de Errors e Exceptions
    error_log("Erro fatal ao instanciar repositórios: " . $e->getMessage() . " na linha " . $e->getLine() . " do arquivo " . $e->getFile());
    ob_clean(); // Limpa o buffer antes de enviar a resposta de erro
    Response::view('errors/500', ['message' => 'Erro interno do servidor: Falha ao carregar dependências essenciais.'], 500)->send();
    exit();
}


// Serviços
$logService = null;
$userService = null;
$productService = null;
$orderService = null;
$cartService = null;
$clientService = null;

try {
    $logService = new LogService($logRepository);
    $userService = new UserService($userRepository, $logService);
    $productService = new ProductService($productRepository);
    $orderService = new OrderService($orderRepository, $productService, $logService, $couponRepository);
    $cartService = new CartService($cartRepository, $productService, $orderService, $logService);
    $clientService = new ClientService($clientRepository, $cartService, $orderService, $logService, $orderRepository);
} catch (\Throwable $e) { // Usar \Throwable para garantir captura de Errors e Exceptions
    error_log("Erro fatal ao instanciar serviços: " . $e->getMessage() . " na linha " . $e->getLine() . " do arquivo " . $e->getFile());
    ob_clean(); // Limpa o buffer antes de enviar a resposta de erro
    Response::view('errors/500', ['message' => 'Erro interno do servidor: Falha ao carregar serviços essenciais.'], 500)->send();
    exit();
}


// Array de dependências globais para injeção
$globalDependencies = [
    'pdo' => $pdo,
    'userRepository' => $userRepository,
    'productRepository' => $productRepository,
    'orderRepository' => $orderRepository,
    'clientRepository' => $clientRepository,
    'cartRepository' => $cartRepository,
    'logRepository' => $logRepository,
    'couponRepository' => $couponRepository,
    'addressRepository' => $addressRepository, // Adiciona AddressRepository
    'logService' => $logService,
    'userService' => $userService,
    'productService' => $productService,
    'orderService' => $orderService,
    'cartService' => $cartService,
    'clientService' => $clientService
];

// --------------------------------------------------------------------
// Cria a instância do roteador
// --------------------------------------------------------------------
$router = new Router();
$router->setGlobalDependencies($globalDependencies); // Passa as dependências para o roteador

// Define os middlewares que o roteador pode usar.
// O nome (chave) será usado nas definições de rota.
$router->middleware('auth', AuthMiddleware::class);
$router->middleware('admin', AdminMiddleware::class);
$router->middleware('seller', SellerMiddleware::class);
$router->middleware('guest', GuestMiddleware::class);
$router->middleware('client', ClientMiddleware::class);

// --------------------------------------------------------------------
// Define as rotas da aplicação
// --------------------------------------------------------------------

/**
 * Função para definir as rotas web.
 * Esta função é chamada aqui para organizar as rotas.
 *
 * @param Router $router O objeto Router para adicionar as rotas.
 */
function defineWebRoutes(Router $router): void
{
    // Rotas públicas (não exigem login)
    $router->get('', [HomeController::class, 'index']);
    $router->get('products', [ProductController::class, 'index']);
    $router->get('products/{id}', [ProductController::class, 'show']);

    // Rotas de Autenticação
    $router->get('login', [AuthController::class, 'showLoginForm'], ['guest']);
    $router->post('login', [AuthController::class, 'login']);
    $router->get('register', [AuthController::class, 'showRegisterForm'], ['guest']);
    $router->post('register', [AuthController::class, 'register']);
    $router->get('logout', [AuthController::class, 'logout'], ['auth']);

    // Rotas protegidas para Clientes (exigem login e role 'client')
    $router->get('cart', [ClientController::class, 'viewCart'], ['auth', 'client']);
    $router->post('cart/add', [ClientController::class, 'addToCart'], ['auth', 'client']);
    $router->post('cart/remove', [ClientController::class, 'removeFromCart'], ['auth', 'client']);
    $router->post('cart/update-quantity', [ClientController::class, 'updateItemQuantity'], ['auth', 'client']);
    $router->get('checkout', [ClientController::class, 'showCheckout'], ['auth', 'client']);
    $router->post('checkout', [ClientController::class, 'processCheckout'], ['auth', 'client']);
    $router->get('orders', [ClientController::class, 'viewOrders'], ['auth', 'client']);
    $router->get('orders/{id}/receipt', [ClientController::class, 'generateReceipt'], ['auth', 'client']);


    // Rotas protegidas para Vendedores (exigem login e role 'seller')
    $router->get('seller/dashboard', [SellerController::class, 'dashboard'], ['auth', 'seller']);
    $router->get('seller/products', [SellerController::class, 'listProducts'], ['auth', 'seller']);
    $router->get('seller/products/create', [SellerController::class, 'createProductForm'], ['auth', 'seller']);
    $router->post('seller/products', [SellerController::class, 'storeProduct'], ['auth', 'seller']);
    $router->get('seller/products/{id}/edit', [SellerController::class, 'editProductForm'], ['auth', 'seller']);
    $router->post('seller/products/{id}', [SellerController::class, 'updateProduct'], ['auth', 'seller']);
    $router->post('seller/products/{id}/delete', [SellerController::class, 'deleteProduct'], ['auth', 'seller']);
    $router->post('seller/products/{id}/discount', [SellerController::class, 'applyDiscount'], ['auth', 'seller']);

    // Rotas protegidas para Administradores (exigem login e role 'admin')
    $router->get('admin/dashboard', [AdminController::class, 'dashboard'], ['auth', 'admin']);
    $router->get('admin/users', [AdminController::class, 'listUsers'], ['auth', 'admin']);
    $router->get('admin/users/{id}', [AdminController::class, 'showUser'], ['auth', 'admin']);
    $router->post('admin/users/{id}', [AdminController::class, 'updateUser'], ['auth', 'admin']);
    $router->post('admin/users/{id}/delete', [AdminController::class, 'deleteUser'], ['auth', 'admin']);
    $router->get('admin/products', [AdminController::class, 'listAllProducts'], ['auth', 'admin']);
    $router->get('admin/orders', [AdminController::class, 'manageOrders'], ['auth', 'admin']);
    $router->post('admin/orders/{id}/status', [AdminController::class, 'updateOrderStatus'], ['auth', 'admin']);
    $router->get('admin/logs', [AdminController::class, 'viewLogs'], ['auth', 'admin']);
} // Fim da função defineWebRoutes

// Chame a função para definir as rotas. As dependências são injetadas pelo Router.
defineWebRoutes($router);

// --------------------------------------------------------------------
// Cria o objeto Request e despacha a requisição
// --------------------------------------------------------------------
$request = new Request();

try {
    $response = $router->dispatch($request);
    $response->send();
} catch (\Throwable $e) { // Captura Throwable para pegar Errors e Exceptions
    // Tratamento de erros centralizado
    error_log("Erro de aplicação: " . $e->getMessage() . " na linha " . $e->getLine() . " do arquivo " . $e->getFile());
    error_log("Stack trace: " . $e->getTraceAsString()); // Adiciona o stack trace completo

    // Limpa o buffer antes de enviar a resposta de erro
    ob_clean();

    // Define o código de status HTTP com base na exceção, se disponível
    $statusCode = $e->getCode() ?: 500;
    if ($statusCode < 100 || $statusCode >= 600) { // Garante que o código é um status HTTP válido
        $statusCode = 500;
    }

    // Renderiza a página de erro apropriada
    $errorResponse = Response::view('errors/500', ['message' => 'Um erro inesperado ocorreu. Por favor, tente novamente mais tarde.'], $statusCode);
    $errorResponse->send();
    exit(); // Garante que o script pare após enviar a resposta de erro
}

// Finaliza o buffer de saída e envia o conteúdo para o navegador
ob_end_flush();