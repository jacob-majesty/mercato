<?php

// Define o título da página
$title = 'Meus Pedidos - Mercato';

// Inicia o buffer de saída para capturar o HTML desta view
ob_start();
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-10 col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0"><i class="fas fa-box-open"></i> Meu Histórico de Pedidos</h4>
            </div>
            <div class="card-body p-4">
                <?php if (isset($error) && !empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($orders) && !empty($orders)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">ID do Pedido</th>
                                    <th scope="col">Data</th>
                                    <th scope="col">Total</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?= htmlspecialchars($order->getId()) ?></td>
                                        <td><?= htmlspecialchars($order->getOrderDate()->format('d/m/Y H:i:s')) ?></td>
                                        <td>R$ <?= number_format($order->getTotalAmount(), 2, ',', '.') ?></td>
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
                                            <a href="/orders/<?= htmlspecialchars($order->getId()) ?>" class="btn btn-info btn-sm me-1" title="Ver Detalhes">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/orders/<?= htmlspecialchars($order->getId()) ?>/receipt" class="btn btn-secondary btn-sm" target="_blank" title="Gerar Comprovante PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <?php if ($order->getStatus() === 'PENDING' || $order->getStatus() === 'PROCESSING'): ?>
                                                <button class="btn btn-warning btn-sm cancel-order-btn ms-1" data-order-id="<?= htmlspecialchars($order->getId()) ?>" title="Cancelar Pedido">
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center" role="alert">
                        Você ainda não fez nenhum pedido. <a href="/" class="alert-link">Comece a comprar agora!</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.cancel-order-btn').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            if (confirm('Tem certeza que deseja cancelar este pedido? Esta ação não pode ser desfeita.')) {
                cancelOrder(orderId);
            }
        });
    });

    function cancelOrder(orderId) {
        fetch(`/orders/${orderId}/cancel`, { // Rota para cancelar pedido
            method: 'POST', // Ou DELETE, dependendo da sua rota
            headers: {
                'Content-Type': 'application/json',
            },
            // Se precisar enviar algum dado no corpo, como um motivo de cancelamento
            // body: JSON.stringify({ reason: 'Mudança de ideia' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Pedido cancelado com sucesso!');
                window.location.reload(); // Recarrega a página para atualizar o status
            } else {
                alert('Erro ao cancelar pedido: ' + data.message);
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
