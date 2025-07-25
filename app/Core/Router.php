<?php

namespace App\Core;

use Exception;

/**
 * Sistema de Roteamento
 * Objetivo: responsável por mapear URLs para controladores e ações específicas, e aplicar middlewares.
 */
class Router
{
    protected array $routes = [];
    protected array $middlewares = [];

    /**
     * Adiciona uma rota GET.
     * @param string $uri
     * @param array $controllerAction Ex: [ProductController::class, 'index']
     * @param array $middlewares Array de nomes de middlewares a serem aplicados.
     */
    public function get(string $uri, array $controllerAction, array $middlewares = []): void
    {
        $this->routes['GET'][$uri] = ['action' => $controllerAction, 'middlewares' => $middlewares];
    }

    /**
     * Adiciona uma rota POST.
     * @param string $uri
     * @param array $controllerAction
     * @param array $middlewares Array de nomes de middlewares a serem aplicados.
     */
    public function post(string $uri, array $controllerAction, array $middlewares = []): void
    {
        $this->routes['POST'][$uri] = ['action' => $controllerAction, 'middlewares' => $middlewares];
    }

    /**
     * Define um middleware.
     * @param string $name Nome do middleware (ex: 'auth', 'admin').
     * @param string $class O FQCN da classe do middleware.
     */
    public function middleware(string $name, string $class): void
    {
        $this->middlewares[$name] = $class;
    }

    /**
     * Despacha a requisição para a rota correta.
     * @param Request $request
     * @return Response
     * @throws Exception Se a rota não for encontrada ou houver erro.
     */
    public function dispatch(Request $request): Response
    {
        $uri = $request->getUri();
        $method = $request->getMethod();

        if (!isset($this->routes[$method][$uri])) {
            // Pode ser uma rota com parâmetros, tentar encontrar
            foreach ($this->routes[$method] as $routeUri => $routeData) {
                // Converte /products/{id} para regex #^/products/(\d+)$#
                $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_]+)', $routeUri);
                if (preg_match('#^' . $pattern . '$#', $uri, $matches)) {
                    array_shift($matches); // Remove a correspondência completa
                    $request->setRouteParams($matches); // Armazena os parâmetros na requisição
                    $controllerAction = $routeData['action'];
                    $routeMiddlewares = $routeData['middlewares'];
                    return $this->callController($request, $controllerAction, $routeMiddlewares);
                }
            }
            throw new Exception("Rota '$uri' não encontrada para o método '$method'.", 404);
        }

        $route = $this->routes[$method][$uri];
        $controllerAction = $route['action'];
        $routeMiddlewares = $route['middlewares'];

        return $this->callController($request, $controllerAction, $routeMiddlewares);
    }

    /**
     * Chama o controlador e executa os middlewares.
     * @param Request $request
     * @param array $controllerAction
     * @param array $routeMiddlewares
     * @return Response
     * @throws Exception
     */
    protected function callController(Request $request, array $controllerAction, array $routeMiddlewares): Response
    {
        // 1. Executar Middlewares
        foreach ($routeMiddlewares as $middlewareName) {
            if (!isset($this->middlewares[$middlewareName])) {
                throw new Exception("Middleware '$middlewareName' não registrado.");
            }
            $middlewareClass = $this->middlewares[$middlewareName];
            $middleware = new $middlewareClass(); // Instancia o middleware
            $middleware->handle($request); // O middleware pode lançar uma exceção ou redirecionar
        }

        // 2. Chamar o Controlador
        list($controllerClass, $method) = $controllerAction;

        if (!class_exists($controllerClass)) {
            throw new Exception("Controlador '$controllerClass' não encontrado.");
        }
        if (!method_exists($controllerClass, $method)) {
            throw new Exception("Método '$method' não encontrado no controlador '$controllerClass'.");
        }

        // Instancia o controlador. Injeção de dependências pode ser feita aqui.
        // Por simplicidade, assumimos que os serviços serão passados para os controladores.
        // Uma forma mais robusta envolveria um Container de Inversão de Controle (IoC).
        $controller = new $controllerClass();

        // Passa a Request para o método do controlador.
        // Parâmetros de rota são acessados via $request->getRouteParams()
        $response = call_user_func_array([$controller, $method], [$request]);

        if (!$response instanceof Response) {
            throw new Exception("A ação do controlador deve retornar uma instância de Response.");
        }

        return $response;
    }
}