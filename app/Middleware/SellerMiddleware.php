<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Authenticator;
use App\Core\Response; // Import Response for redirection

class SellerMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): bool // Alterado o tipo de retorno para bool
    {
        error_log("SellerMiddleware: Executando para URI '" . $request->getUri() . "'");

        if (!Authenticator::check() || !Authenticator::isSeller()) {
            error_log("SellerMiddleware: Acesso negado. Usuário não é vendedor ou não autenticado. Redirecionando para /login.");
            Response::redirect('/login')->send(); // Ou para uma página de 403, dependendo da UX
            exit();
        }
        error_log("SellerMiddleware: Usuário é vendedor e autenticado. Permissão concedida. Retornando true.");
        return true; // Explicitamente retorna true quando o acesso é concedido
    }
}