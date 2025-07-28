<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Authenticator;
use App\Core\Response; // Import Response for redirection

class AdminMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): bool // Alterado o tipo de retorno para bool
    {
        error_log("AdminMiddleware: Executando para URI '" . $request->getUri() . "'");

        if (!Authenticator::check() || !Authenticator::isAdmin()) {
            error_log("AdminMiddleware: Acesso negado. Usuário não é admin ou não autenticado. Redirecionando para /login.");
            Response::redirect('/login')->send(); // Ou para uma página de 403, dependendo da UX
            exit();
        }
        error_log("AdminMiddleware: Usuário é admin e autenticado. Permissão concedida. Retornando true.");
        return true; // Explicitamente retorna true quando o acesso é concedido
    }
}