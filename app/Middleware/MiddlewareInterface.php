<?php

namespace App\Middleware;

use App\Core\Request;

interface MiddlewareInterface
{
    public function handle(Request $request): bool; // Mudar de void para bool
}