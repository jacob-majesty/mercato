<?php

namespace App\Core;

use Exception;

/**
 * Abstração da Resposta
 * Para padronizar as respostas HTTP.
 */

class Response
{
    protected string $content;
    protected int $statusCode;
    protected array $headers = [];

    public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function addHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        echo $this->content;
    }

    public static function json(array $data, int $statusCode = 200): self
    {
        return new self(json_encode($data), $statusCode, ['Content-Type' => 'application/json']);
    }

    public static function redirect(string $url, int $statusCode = 302): self
    {
        return new self('', $statusCode, ['Location' => $url]);
    }

    // Método para renderizar views (precisa de um sistema de templates ou incluir arquivos)
    public static function view(string $viewPath, array $data = [], int $statusCode = 200): self
    {
        // Certifique-se de que o caminho base das views está configurado corretamente
        $fullPath = __DIR__ . '/../views/' . $viewPath . '.php'; // Assumindo pasta 'views'
        if (!file_exists($fullPath)) {
            throw new Exception("View not found: $fullPath");
        }

        // Inicia o buffer de saída
        ob_start();
        // Extrai os dados para que as variáveis fiquem disponíveis na view
        extract($data);
        // Inclui o arquivo da view
        require $fullPath;
        // Obtém o conteúdo do buffer
        $content = ob_get_clean();

        return new self($content, $statusCode, ['Content-Type' => 'text/html']);
    }
}