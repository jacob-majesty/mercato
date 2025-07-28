<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Authenticator;
use App\Core\Response; // Import Response for redirection

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): bool // Alterado o tipo de retorno para bool
    {
        error_log("AuthMiddleware: Executando para URI '" . $request->getUri() . "'");

        if (!Authenticator::check()) {
            error_log("AuthMiddleware: Usuário não autenticado. Redirecionando para /login.");
            Response::redirect('/login')->send();
            exit();
        }
        error_log("AuthMiddleware: Usuário autenticado. Permissão concedida. Retornando true.");
        return true; // Explicitamente retorna true quando o acesso é concedido
    }
}