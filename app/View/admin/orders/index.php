<?php

// Define o título da página
$title = 'Todos os Pedidos (Admin) - Mercato';

// Inicia o buffer de saída para capturar o HTML desta view
ob_start();
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-danger text-white text-center">
                <h4 class="mb-0"><i class="fas fa-clipboard-list"></i> Todos os Pedidos (Administração)</h4>
            </div>
            <div class="card-body p-4">
                <?php if (isset($error) && !empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($success_message) && !empty($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($success_message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($orders)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">ID Pedido</th>
                                    <th scope="col">ID Cliente</th>
                                    <th scope="col">Data</th>
                                    <th scope="col">Total</th>
                                    <th scope="col">Método Pag.</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?= htmlspecialchars($order->getId()) ?></td>
                                        <td><?= htmlspecialchars($order->getClientId()) ?></td>
                                        <td><?= htmlspecialchars($order->getOrderDate()->format('d/m/Y H:i:s')) ?></td>
                                        <td>R$ <?= number_format($order->getTotalAmount(), 2, ',', '.') ?></td>
                                        <td><?= htmlspecialchars($order->getPaymentMethod()) ?></td>
                                        <td>
                                            <span class="badge 
                                                <?php
                                                switch ($order->getStatus()) {
                                                    case 'COMPLETED': echo 'bg-success'; break;
                                                    case 'PENDING': echo 'bg-warning text-dark'; break;
                                                    case 'PROCESSING': echo 'bg-info text-dark'; break;
                                                    case 'SHIPPED': echo 'bg-primary'; break;
                                                    case 'DELIVERED': echo 'bg-success'; break;
                                                    case 'CANCELLED': echo 'bg-danger'; break;
                                                    case 'REFUNDED': echo 'bg-secondary'; break;
                                                    default: echo 'bg-light text-dark'; break;
                                                }
                                                ?>">
                                                <?= htmlspecialchars($order->getStatus()) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="/admin/orders/<?= htmlspecialchars($order->getId()) ?>" class="btn btn-info btn-sm me-1" title="Ver Detalhes">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn btn-warning btn-sm update-status-btn" data-order-id="<?= htmlspecialchars($order->getId()) ?>" title="Atualizar Status">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center" role="alert">
                        Nenhum pedido registrado no sistema.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.update-status-btn').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            const newStatus = prompt('Digite o novo status para o pedido ' + orderId + ' (Ex: PROCESSING, SHIPPED, DELIVERED, CANCELLED):');

            if (newStatus) {
                updateOrderStatus(orderId, newStatus.toUpperCase()); // Converte para maiúsculas
            }
        });
    });

    function updateOrderStatus(orderId, newStatus) {
        fetch(`/admin/orders/${orderId}/status`, { // Assumindo uma rota de admin para atualizar status
            method: 'POST', // Ou PUT/PATCH
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Status do pedido atualizado com sucesso!');
                window.location.reload();
            } else {
                alert('Erro ao atualizar status: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Ocorreu um erro ao comunicar com o servidor.');
        });
    }
});
</script>

<?php
// Obtém o conteúdo do buffer e o passa para a variável $content
$content = ob_get_clean();

// Inclui o layout principal
require __DIR__ . '/../layout/main.php';
?>
