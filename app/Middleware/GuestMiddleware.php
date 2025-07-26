<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Authenticator;
use App\Core\Response; // Para redirecionamento
use Exception;

/**
 * Class GuestMiddleware
 * @package App\Middleware
 *
 * Middleware para garantir que apenas usuários NÃO autenticados possam acessar uma rota.
 * Se o usuário estiver logado, ele será redirecionado.
 */
class GuestMiddleware implements MiddlewareInterface
{
    /**
     * Manipula a requisição antes que ela chegue ao controlador.
     * Redireciona o usuário se ele já estiver autenticado.
     *
     * @param Request $request A requisição HTTP atual.
     * @return bool Retorna true se a requisição deve continuar, false se foi redirecionada/interrompida.
     * @throws Exception Se houver um erro inesperado.
     */
    public function handle(Request $request): bool // Alterado o tipo de retorno para bool
    {
        error_log("GuestMiddleware: Executando para URI '" . $request->getUri() . "'");

        // Verifica se o usuário já está logado
        if (Authenticator::check()) {
            $userRole = Authenticator::getUserRole();
            $userId = Authenticator::getUserId();
            error_log("GuestMiddleware: Usuário ID {$userId} (Role: {$userRole}) já autenticado. Redirecionando para /.");
            
            // Se estiver logado, redireciona para a página inicial (ou dashboard)
            // e encerra a execução para que o controlador não seja chamado.
            Response::redirect('/')->send(); // Redireciona para a home page
            exit(); // Importante para parar a execução do script
        }
        error_log("GuestMiddleware: Usuário não autenticado. Permissão concedida. Retornando true.");
        return true; // Explicitamente retorna true quando o acesso é concedido
    }
}
