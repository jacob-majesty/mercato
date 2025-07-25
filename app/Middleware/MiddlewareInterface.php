<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

/**
 * Verificação de Permissões
 * 
 */

interface MiddlewareInterface
{
    /**
     * Manipula a requisição antes que ela chegue ao controlador.
     * Pode lançar uma exceção, redirecionar, ou modificar a requisição/resposta.
     * @param Request $request
     * @throws \Exception Se a condição do middleware não for atendida (ex: não autenticado).
     */
    public function handle(Request $request): void;
}