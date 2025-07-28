<?php

namespace App\Core;

/**
 * Abstração da Requisição
 * Ajuda a encapsular os dados da requisição HTTP.
 */

class Request
{
    protected string $uri;
    protected string $method;
    protected array $params; // GET, POST, etc.
    protected array $routeParams = []; // Parâmetros extraídos da rota (ex: /products/{id})

    public function __construct()
    {
        $this->uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->params = $_REQUEST; // Combina GET e POST por simplicidade
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function get(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }

    public function post(string $key, $default = null)
    {
        if ($this->method === 'POST') {
            return $_POST[$key] ?? $default;
        }
        return $default;
    }

    public function all(): array
    {
        return $this->params;
    }

    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

   /**
     * Obtém um parâmetro específico da rota por chave (nome ou índice numérico).
     * Ex: se a rota é /products/{id}, o parâmetro pode ser acessado como getRouteParam('id').
     * Se a rota for /items/1/2 (sem nomes), pode ser acessado como getRouteParam(0) para '1'.
     * @param string|int $key O nome ou índice do parâmetro da rota.
     * @param mixed $default O valor padrão a ser retornado se a chave não existir.
     * @return mixed O valor do parâmetro da rota, ou o valor padrão.
     */
    public function getRouteParam(string|int $key, $default = null)
    {
        return $this->routeParams[$key] ?? $default;
    }

    /**
     * Retorna o corpo da requisição bruta.
     * Útil para requisições com Content-Type: application/json.
     * @return string|null O corpo da requisição ou null se não houver.
     */
    public function getBody(): ?string
    {
        return file_get_contents('php://input');
    }

}