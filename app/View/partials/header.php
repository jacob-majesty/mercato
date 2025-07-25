<?php

// Este partial deve ser incluído no início do <body> do seu layout principal.
// Ele contém a barra de navegação e a abertura do container principal.
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">Mercato</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/products">Produtos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/cart">Carrinho</a>
                </li>
                <?php if (\App\Core\Authenticator::check()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/orders">Meus Pedidos</a>
                    </li>
                    <?php if (\App\Core\Authenticator::isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/dashboard">Admin Dashboard</a>
                        </li>
                    <?php elseif (\App\Core\Authenticator::isSeller()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/seller/dashboard">Vendedor Dashboard</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if (\App\Core\Authenticator::check()): ?>
                    <li class="nav-item">
                        <span class="nav-link text-white">Olá, <?= htmlspecialchars(\App\Core\Authenticator::user()->getFirstName() ?? 'Usuário') ?>!</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light ms-2" href="/logout">Sair</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light" href="/login">Entrar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light ms-2" href="/register">Registrar</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <!-- O conteúdo específico da página será inserido aqui -->
