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
     * Obtém um parâmetro específico da rota por índice.
     * Ex: se a rota é /products/{id}, o primeiro parâmetro de rota seria o ID.
     */
    public function getRouteParam(int $index, $default = null)
    {
        return $this->routeParams[$index] ?? $default;
    }
}