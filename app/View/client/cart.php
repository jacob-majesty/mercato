<?php


// Define o título da página
$title = 'Meu Carrinho - Mercato';

// Inicia o buffer de saída para capturar o HTML desta view
ob_start();
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-10 col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0"><i class="fas fa-shopping-cart"></i> Meu Carrinho de Compras</h4>
            </div>
            <div class="card-body p-4">
                <?php if (isset($error) && !empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($cart) && !empty($cart->getItems())): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">Produto</th>
                                    <th scope="col" class="text-center">Preço Unitário</th>
                                    <th scope="col" class="text-center">Quantidade</th>
                                    <th scope="col" class="text-center">Total</th>
                                    <th scope="col" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart->getItems() as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item->getProductName()) ?></td>
                                        <td class="text-center">R$ <?= number_format($item->getUnitPrice(), 2, ',', '.') ?></td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center align-items-center">
                                                <button class="btn btn-sm btn-outline-secondary update-quantity-btn" data-product-id="<?= $item->getProductId() ?>" data-action="decrement">-</button>
                                                <input type="number" class="form-control form-control-sm mx-2 text-center" style="width: 70px;" value="<?= htmlspecialchars($item->getQuantity()) ?>" min="1" data-product-id="<?= $item->getProductId() ?>">
                                                <button class="btn btn-sm btn-outline-secondary update-quantity-btn" data-product-id="<?= $item->getProductId() ?>" data-action="increment">+</button>
                                            </div>
                                        </td>
                                        <td class="text-center">R$ <?= number_format($item->getTotal(), 2, ',', '.') ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-danger btn-sm remove-item-btn" data-product-id="<?= $item->getProductId() ?>">
                                                <i class="fas fa-trash-alt"></i> Remover
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total do Carrinho:</th>
                                    <th colspan="2" class="text-center fs-5 text-primary">R$ <?= number_format($cart->getTotalAmount(), 2, ',', '.') ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="/" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Continuar Comprando</a>
                        <a href="/checkout" class="btn btn-success btn-lg">Finalizar Compra <i class="fas fa-arrow-right"></i></a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center" role="alert">
                        Seu carrinho está vazio. <a href="/" class="alert-link">Comece a adicionar produtos!</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lógica para atualizar a quantidade de um item no carrinho
    document.querySelectorAll('.update-quantity-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const action = this.dataset.action;
            const input = document.querySelector(`input[data-product-id="${productId}"]`);
            let newQuantity = parseInt(input.value);

            if (action === 'increment') {
                newQuantity++;
            } else if (action === 'decrement') {
                newQuantity--;
            }

            if (newQuantity < 1) {
                // Se a quantidade for menor que 1, remova o item
                if (confirm('Deseja remover este item do carrinho?')) {
                    removeItem(productId);
                } else {
                    input.value = 1; // Volta para 1 se o usuário cancelar
                }
            } else {
                updateItemQuantity(productId, newQuantity);
            }
        });
    });

    // Lógica para remover um item do carrinho
    document.querySelectorAll('.remove-item-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            if (confirm('Tem certeza que deseja remover este item do carrinho?')) {
                removeItem(productId);
            }
        });
    });

    // Função AJAX para atualizar a quantidade
    function updateItemQuantity(productId, quantity) {
        fetch('/cart/update-quantity', { // Rota para atualizar quantidade
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ product_id: productId, quantity: quantity })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload(); // Recarrega a página para refletir as mudanças
            } else {
                alert('Erro ao atualizar quantidade: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Ocorreu um erro ao comunicar com o servidor.');
        });
    }

    // Função AJAX para remover item
    function removeItem(productId) {
        fetch('/cart/remove', { // Rota para remover item
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload(); // Recarrega a página
            } else {
                alert('Erro ao remover item: ' + data.message);
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
