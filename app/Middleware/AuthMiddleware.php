<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Authenticator;
use App\Core\Response; // Para redirecionamento
use Exception;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        if (!Authenticator::check()) {
            // Se não estiver autenticado, redireciona para a página de login
            // E encerra a execução para que o controlador não seja chamado.
            Response::redirect('/login')->send();
            exit(); // Importante para parar a execução
        }
    }
}