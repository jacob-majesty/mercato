<?php

// Define o título da página
$title = 'Dashboard do Administrador - Mercato';

// Inicia o buffer de saída para capturar o HTML desta view
ob_start();
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-10 col-lg-9">
        <div class="card shadow-sm">
            <div class="card-header bg-danger text-white text-center">
                <h4 class="mb-0"><i class="fas fa-cogs"></i> Mercato - Dashboard do Administrador</h4>
            </div>
            <div class="card-body p-4">
                <h5 class="mb-4">Bem-vindo, Administrador!</h5>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-users"></i> Total de Usuários</h5>
                                <p class="card-text fs-4"><?= htmlspecialchars($totalUsers ?? 0) ?></p>
                                <a href="/admin/users" class="btn btn-light btn-sm">Gerenciar Usuários</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-box-open"></i> Total de Produtos</h5>
                                <p class="card-text fs-4"><?= htmlspecialchars($totalProducts ?? 0) ?></p>
                                <a href="/admin/products" class="btn btn-light btn-sm">Gerenciar Produtos</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-shopping-bag"></i> Total de Pedidos</h5>
                                <p class="card-text fs-4"><?= htmlspecialchars($totalOrders ?? 0) ?></p>
                                <a href="/admin/orders" class="btn btn-light btn-sm">Gerenciar Pedidos</a>
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="mb-3">Ações Rápidas:</h5>
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    <a href="/admin/users" class="btn btn-outline-primary"><i class="fas fa-user-cog"></i> Gerenciar Usuários</a>
                    <a href="/admin/products" class="btn btn-outline-info"><i class="fas fa-boxes"></i> Gerenciar Produtos</a>
                    <a href="/admin/orders" class="btn btn-outline-success"><i class="fas fa-clipboard-list"></i> Gerenciar Pedidos</a>
                    <a href="/admin/logs" class="btn btn-outline-secondary"><i class="fas fa-history"></i> Ver Logs</a>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
// Obtém o conteúdo do buffer e o passa para a variável $content
$content = ob_get_clean();

// Inclui o layout principal
require __DIR__ . '/../layout/main.php';
?>
