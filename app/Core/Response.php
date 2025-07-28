<?php

namespace App\Core;

use Exception; // Para lançar exceções em caso de view não encontrada

/**
 * Class Response
 * @package App\Core
 *
 * Gerencia as respostas HTTP da aplicação.
 */
class Response
{
    private string $content;
    private int $statusCode;
    private array $headers = [];

    public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * Define o conteúdo da resposta.
     * @param string $content
     * @return self
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Define o código de status HTTP da resposta.
     * @param int $statusCode
     * @return self
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Adiciona um cabeçalho HTTP à resposta.
     * @param string $name
     * @param string $value
     * @return self
     */
    public function addHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Envia os cabeçalhos e o conteúdo da resposta ao navegador.
     */
    public function send(): void
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
        echo $this->content;
    }

    /**
     * Retorna uma resposta de view (HTML).
     * @param string $viewName O nome da view (ex: 'home', 'auth/login', 'errors/404').
     * @param array $data Dados a serem passados para a view.
     * @param int $statusCode Código de status HTTP.
     * @return Response
     * @throws Exception Se a view não for encontrada.
     */
    public static function view(string $viewName, array $data = [], int $statusCode = 200): Response
    {
        // Define o caminho base para as views.
        // Se Response.php está em 'app/Core/', precisamos subir um nível (../)
        // para 'app/' e então ir para 'View/'.
        $viewsPath = __DIR__ . '/../View/'; // CORRIGIDO: Ajustado o caminho para app/View/

        $filePath = $viewsPath . str_replace('.', '/', $viewName) . '.php';

        if (!file_exists($filePath)) {
            throw new Exception("View not found: " . $filePath);
        }

        // Extrai os dados para que as variáveis fiquem disponíveis na view
        extract($data);

        // Inicia o buffer de saída
        ob_start();
        require $filePath;
        $content = ob_get_clean();

        return new self($content, $statusCode);
    }

    /**
     * Retorna uma resposta JSON.
     * @param array $data Dados a serem codificados como JSON.
     * @param int $statusCode Código de status HTTP.
     * @return Response
     */
    public static function json(array $data, int $statusCode = 200): Response
    {
        $response = new self(json_encode($data), $statusCode);
        $response->addHeader('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Retorna uma resposta de redirecionamento.
     * @param string $url A URL para a qual redirecionar.
     * @param int $statusCode Código de status HTTP para o redirecionamento (ex: 302 Found).
     * @return Response
     */
    public static function redirect(string $url, int $statusCode = 302): Response
    {
        $response = new self('', $statusCode);
        $response->addHeader('Location', $url);
        return $response;
    }
}
