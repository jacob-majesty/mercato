<?php

namespace App\Core;

use Exception;
use Throwable; // Para capturar qualquer tipo de erro lançável
use ReflectionClass; // Adicionar para clareza
use ReflectionParameter; // Adicionar para clareza
use ReflectionNamedType; // Adicionar para clareza

/**
 * Class Router
 * @package App\Core
 *
 * Gerencia o roteamento de URLs para Controladores e ações.
 */
class Router
{
    protected array $routes = [];
    protected array $middlewares = [];
    protected array $globalDependencies = []; // Para armazenar todas as dependências

    /**
     * Define uma rota GET.
     * @param string $uri
     * @param array $handler Array [Controller::class, 'method']
     * @param array $middlewares Array de nomes de middlewares a aplicar
     */
    public function get(string $uri, array $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $uri, $handler, $middlewares);
    }

    /**
     * Define uma rota POST.
     * @param string $uri
     * @param array $handler Array [Controller::class, 'method']
     * @param array $middlewares Array de nomes de middlewares a aplicar
     */
    public function post(string $uri, array $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $uri, $handler, $middlewares);
    }

    /**
     * Adiciona uma rota ao roteador.
     * @param string $method
     * @param string $uri
     * @param array $handler
     * @param array $middlewares
     */
    protected function addRoute(string $method, string $uri, array $handler, array $middlewares): void
    {
        // Normaliza a URI para remover barras extras no final, exceto para a raiz
        $uri = ($uri === '') ? '/' : rtrim($uri, '/');
        $this->routes[$method][$uri] = ['handler' => $handler, 'middlewares' => $middlewares];
    }

    /**
     * Define um middleware nomeado.
     * @param string $name
     * @param string $class O nome da classe do middleware.
     * @throws Exception Se a classe do middleware não existir ou não implementar MiddlewareInterface.
     */
    public function middleware(string $name, string $class): void
    {
        if (!class_exists($class)) {
            throw new Exception("Middleware class '{$class}' not found.");
        }
        // Opcional: Verificar se implementa uma interface MiddlewareInterface
        // if (!in_array(MiddlewareInterface::class, class_implements($class))) {
        //     throw new Exception("Middleware class '{$class}' must implement MiddlewareInterface.");
        // }
        $this->middlewares[$name] = $class;
    }

    /**
     * Define as dependências globais que serão injetadas nos controladores.
     * @param array $dependencies
     */
    public function setGlobalDependencies(array $dependencies): void
    {
        $this->globalDependencies = $dependencies;
    }

    /**
     * Despacha a requisição para o controlador e método apropriados.
     * @param Request $request
     * @return Response
     * @throws Exception Se a rota não for encontrada, o controlador ou método não existirem, ou o middleware falhar.
     */
    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $uri = $request->getUri();

        // Normaliza a URI de entrada
        $uri = ($uri === '') ? '/' : rtrim($uri, '/'); // Garante que '/' seja '/' e 'login/' seja 'login'
        $uri = strtok($uri, '?'); // Remove a query string

        // ==== DEBUGGING: Log da URI de entrada e rotas disponíveis ====
        error_log("Incoming URI: '" . $uri . "' (Method: " . $method . ")");
        error_log("Available " . $method . " routes: " . json_encode(array_keys($this->routes[$method] ?? [])));
        // =============================================================

        // Tenta encontrar uma rota com correspondência exata primeiro
        if (isset($this->routes[$method][$uri])) {
            $routeData = $this->routes[$method][$uri];
            // ==== DEBUGGING: Log de correspondência exata ====
            error_log("Exact match found for URI: '" . $uri . "'");
            // =====================================
            return $this->processRoute($request, $routeData, []); // Sem parâmetros de rota para correspondência exata
        }

        // Se não houver correspondência exata, tenta com padrões regex para parâmetros
        foreach ($this->routes[$method] ?? [] as $routeUri => $routeData) {
            // Pula rotas que não contêm parâmetros (já tratadas pela correspondência exata)
            if (strpos($routeUri, '{') === false) {
                continue;
            }

            // Converte a URI da rota para uma regex para capturar parâmetros
            $pattern = '@^' . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routeUri) . '$@';

            // ==== DEBUGGING: Log de tentativa de regex ====
            error_log("Trying regex pattern: '" . $pattern . "' for route: '" . $routeUri . "'");
            // ======================================

            if (preg_match($pattern, $uri, $matches)) {
                // Extrai parâmetros da URI
                $routeParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                // ==== DEBUGGING: Log de correspondência regex ====
                error_log("Regex match found for URI: '" . $uri . "' with route: '" . $routeUri . "'. Params: " . json_encode($routeParams));
                // =====================================
                return $this->processRoute($request, $routeData, $routeParams);
            }
        }

        // Se nenhuma rota corresponder
        throw new Exception("Página não encontrada.", 404);
    }

    /**
     * Processa a rota após uma correspondência bem-sucedida.
     * @param Request $request
     * @param array $routeData
     * @param array $routeParams
     * @return Response
     * @throws Exception
     */
    protected function processRoute(Request $request, array $routeData, array $routeParams): Response
    {
        $request->setRouteParams($routeParams);

        $handler = $routeData['handler'];
        $middlewaresToApply = $routeData['middlewares'];

        // Aplica os middlewares
        foreach ($middlewaresToApply as $middlewareName) {
            if (!isset($this->middlewares[$middlewareName])) {
                throw new Exception("Middleware '{$middlewareName}' não definido.");
            }
            $middlewareClass = $this->middlewares[$middlewareName];
            $middlewareInstance = new $middlewareClass();
            // O middleware deve retornar explicitamente true para continuar
            if (!$middlewareInstance->handle($request)) {
                // Se o middleware retornar false, a requisição é negada (e o middleware já deve ter lidado com o redirecionamento/saída)
                // Se o middleware não chamou exit(), então lançamos uma exceção 403 aqui.
                // No seu caso, os middlewares chamam exit(), então esta linha pode não ser atingida.
                throw new Exception("Acesso negado por middleware: " . $middlewareName, 403);
            }
        }

        $controllerClass = $handler[0];
        $method = $handler[1];

        if (!class_exists($controllerClass)) {
            throw new Exception("Controlador '{$controllerClass}' não encontrado.", 404);
        }

        $reflectionClass = new ReflectionClass($controllerClass);
        $constructor = $reflectionClass->getConstructor();

        $controllerInstance = null;
        if ($constructor) {
            $params = $constructor->getParameters();
            $dependenciesToInject = [];
            foreach ($params as $param) {
                $paramType = $param->getType();
                
                // Verifica se o parâmetro tem um tipo declarado e se é um ReflectionNamedType
                if ($paramType instanceof ReflectionNamedType) {
                    // Agora podemos usar isBuiltin() no objeto ReflectionNamedType
                    if (!$paramType->isBuiltin()) { // Verifica se NÃO é um tipo built-in (string, int, bool, etc.)
                        $paramClassName = $paramType->getName();
                        $dependencyKey = lcfirst(basename(str_replace('\\', '/', $paramClassName)));
                        
                        if (isset($this->globalDependencies[$dependencyKey])) {
                            $dependenciesToInject[] = $this->globalDependencies[$dependencyKey];
                        } else {
                            throw new Exception("Dependência '{$paramClassName}' para o construtor de '{$controllerClass}' não encontrada.");
                        }
                    } else {
                        // É um tipo built-in (string, int, bool, etc.)
                        throw new Exception("Parâmetro built-in '{$param->getName()}' no construtor de '{$controllerClass}' não pode ser injetado automaticamente via dependências globais. Apenas objetos são injetáveis desta forma.");
                    }
                } else {
                    // Parâmetro sem tipo ou tipo inválido (ex: ReflectionUnionType, ReflectionIntersectionType, ou null)
                    throw new Exception("Parâmetro '{$param->getName()}' no construtor de '{$controllerClass}' não tem tipo de classe declarado ou é um tipo complexo não suportado para injeção automática.");
                }
            }
            $controllerInstance = $reflectionClass->newInstanceArgs($dependenciesToInject);
        } else {
            $controllerInstance = new $controllerClass();
        }

        if (!method_exists($controllerInstance, $method)) {
            throw new Exception("Método '{$method}' não encontrado no controlador '{$controllerClass}'.", 404);
        }

        return $controllerInstance->$method($request);
    }
}
