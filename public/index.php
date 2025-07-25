<?php

// Inicia o sistema de sessões globalmente
use App\Core\Authenticator;
Authenticator::startSession();

// Inclui o autoloader do Composer (SE VOCÊ ESTIVER USANDO COMPOSER)
// require_once __DIR__ . '/../vendor/autoload.php';

// Se não estiver usando Composer, inclua seus arquivos de configuração e classes essenciais
// ATENÇÃO: Para projetos maiores, um autoloader como o do Composer é MANDATÓRIO.
// As includes manuais são apenas para demonstração de conceitos sem Composer.
require_once __DIR__ . '/../src/Core/Router.php';
require_once __DIR__ . '/../src/Core/Request.php';
require_once __DIR__ . '/../src/Core/Response.php';
require_once __DIR__ . '/../src/Core/Authenticator.php';

require_once __DIR__ . '/../src/Repository/ProductRepositoryInterface.php';
require_once __DIR__ . '/../src/Repository/ProductRepository.php';
require_once __DIR__ . '/../src/Service/ProductService.php';
require_once __DIR__ . '/../src/Config/Database.php';
require_once __DIR__ . '/../src/Model/Product.php';
require_once __DIR__ . '/../src/Model/User.php'; // Para Authenticator::user()

// Middlewares
require_once __DIR__ . '/../src/Middleware/MiddlewareInterface.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../src/Middleware/AdminMiddleware.php';
// ... outros middlewares

// Controladores (incluir todos que serão usados nas rotas)
require_once __DIR__ . '/../src/Controller/HomeController.php';
require_once __DIR__ . '/../src/Controller/ProductController.php';
require_once __DIR__ . '/../src/Controller/AuthController.php';
// require_once __DIR__ . '/../src/Controller/ClientController.php';
// require_once __DIR__ . '/../src/Controller/SellerController.php';
// require_once __DIR__ . '/../src/Controller/AdminController.php';


use App\Core\Router;
use App\Core\Request;
use App\Core\Response;

// 1. Cria a instância do roteador
$router = new Router();

// 2. Carrega as definições de rotas (web.php e api.php)
require_once __DIR__ . '/../src/routes/web.php';
// defineApiRoutes($router); // Se você tiver rotas de API

// Chama a função para definir as rotas web
defineWebRoutes($router);

// 3. Cria o objeto Request
$request = new Request();

// 4. Despacha a requisição
try {
    $response = $router->dispatch($request);
    $response->send();
} catch (Exception $e) {
    // Tratamento de erros centralizado
    error_log("Erro de aplicação: " . $e->getMessage() . " na linha " . $e->getLine() . " do arquivo " . $e->getFile());

    if ($e->getCode() === 404) {
        Response::view('errors/404', ['message' => $e->getMessage()], 404)->send();
    } else {
        Response::view('errors/500', ['message' => 'Um erro inesperado ocorreu. Por favor, tente novamente mais tarde.'], 500)->send();
    }
}