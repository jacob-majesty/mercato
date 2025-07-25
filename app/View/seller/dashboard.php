<?php

// Define o título da página
$title = 'Dashboard do Vendedor - Mercato';

// Inicia o buffer de saída para capturar o HTML desta view
ob_start();
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-10 col-lg-9">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0"><i class="fas fa-chart-line"></i> Dashboard do Vendedor</h4>
            </div>
            <div class="card-body p-4">
                <h5 class="mb-4">Bem-vindo, Vendedor ID: <?= htmlspecialchars($sellerId ?? 'N/A') ?></h5>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-box-open"></i> Meus Produtos</h5>
                                <p class="card-text fs-4"><?= count($products ?? []) ?></p>
                                <a href="/seller/products" class="btn btn-light btn-sm">Gerenciar Produtos</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-dollar-sign"></i> Vendas Recentes</h5>
                                <p class="card-text fs-4">Ainda não implementado</p>
                                <!-- Você pode adicionar um link para ver vendas específicas do vendedor aqui -->
                                <button class="btn btn-light btn-sm" disabled>Ver Vendas</button>
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="mb-3">Atividade Recente (Logs):</h5>
                <?php if (!empty($logs)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Ação</th>
                                    <th>Data/Hora</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($log->getType()) ?></span></td>
                                        <td><?= htmlspecialchars($log->getAction()) ?></td>
                                        <td><?= htmlspecialchars($log->getTimestamp()->format('d/m/Y H:i:s')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center" role="alert">
                        Nenhuma atividade recente registrada.
                    </div>
                <?php endif; ?>

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
