<?php

// Define o título da página
$title = 'Logs do Sistema - Mercato';

// Inicia o buffer de saída para capturar o HTML desta view
ob_start();
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-danger text-white text-center">
                <h4 class="mb-0"><i class="fas fa-history"></i> Logs do Sistema</h4>
            </div>
            <div class="card-body p-4">
                <?php if (isset($error) && !empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($logs)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Tipo</th>
                                    <th scope="col">Ação</th>
                                    <th scope="col">ID Usuário</th>
                                    <th scope="col">Detalhes</th>
                                    <th scope="col">Data/Hora</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($log->getId()) ?></td>
                                        <td>
                                            <span class="badge 
                                                <?php
                                                switch ($log->getType()) {
                                                    case 'ERROR': echo 'bg-danger'; break;
                                                    case 'WARNING': echo 'bg-warning text-dark'; break;
                                                    case 'INFO': echo 'bg-info text-dark'; break;
                                                    case 'AUTH': echo 'bg-primary'; break;
                                                    case 'CLIENT': echo 'bg-success'; break;
                                                    case 'SELLER': echo 'bg-secondary'; break;
                                                    case 'PRODUCT': echo 'bg-dark'; break;
                                                    case 'CART': echo 'bg-light text-dark border'; break;
                                                    case 'ORDER': echo 'bg-success'; break;
                                                    case 'PURCHASE': echo 'bg-success'; break;
                                                    default: echo 'bg-light text-dark'; break;
                                                }
                                                ?>">
                                                <?= htmlspecialchars($log->getType()) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($log->getAction()) ?></td>
                                        <td><?= htmlspecialchars($log->getUserId() ?? 'N/A') ?></td>
                                        <td>
                                            <?php
                                                $details = $log->getDetails();
                                                if ($details) {
                                                    // Tenta decodificar JSON para uma exibição mais legível
                                                    $decodedDetails = json_decode($details, true);
                                                    if (json_last_error() === JSON_ERROR_NONE) {
                                                        echo '<pre class="mb-0 small">' . htmlspecialchars(json_encode($decodedDetails, JSON_PRETTY_PRINT)) . '</pre>';
                                                    } else {
                                                        echo htmlspecialchars($details);
                                                    }
                                                } else {
                                                    echo 'N/A';
                                                }
                                            ?>
                                        </td>
                                        <td><?= htmlspecialchars($log->getTimestamp()->format('d/m/Y H:i:s')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center" role="alert">
                        Nenhum log registrado no sistema.
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
