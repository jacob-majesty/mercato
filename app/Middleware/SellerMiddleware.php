<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Authenticator;
use App\Core\Response;
use Exception;

class SellerMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        if (!Authenticator::isSeller()) {
            // Se não for Seller, redireciona ou mostra erro 403 (Proibido)
            Response::view('errors/403', ['message' => 'Acesso negado. Você não tem permissão para esta ação.'], 403)->send();
            exit();
        }
    }
}