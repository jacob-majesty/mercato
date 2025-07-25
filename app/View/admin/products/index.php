<?php

// Define o título da página
$title = 'Todos os Produtos (Admin) - Mercato';

// Inicia o buffer de saída para capturar o HTML desta view
ob_start();
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-danger text-white text-center">
                <h4 class="mb-0"><i class="fas fa-boxes"></i> Todos os Produtos (Administração)</h4>
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

                <?php if (!empty($products)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Nome</th>
                                    <th scope="col">Preço</th>
                                    <th scope="col">Estoque</th>
                                    <th scope="col">Vendedor ID</th>
                                    <th scope="col">Categoria</th>
                                    <th scope="col" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($product->getId()) ?></td>
                                        <td><?= htmlspecialchars($product->getName()) ?></td>
                                        <td>R$ <?= number_format($product->getPrice(), 2, ',', '.') ?></td>
                                        <td>
                                            <span class="badge <?= $product->getStock() > 0 ? 'bg-success' : 'bg-danger' ?>">
                                                <?= htmlspecialchars($product->getStock()) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($product->getSellerId()) ?></td>
                                        <td><?= htmlspecialchars($product->getCategory()) ?></td>
                                        <td class="text-center">
                                            <!-- Admin pode editar produtos de qualquer vendedor -->
                                            <a href="/seller/products/<?= htmlspecialchars($product->getId()) ?>/edit" class="btn btn-warning btn-sm me-1" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-danger btn-sm delete-product-btn" data-product-id="<?= htmlspecialchars($product->getId()) ?>" title="Excluir">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center" role="alert">
                        Nenhum produto cadastrado no sistema.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-product-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            if (confirm('Tem certeza que deseja excluir este produto? Esta ação é irreversível.')) {
                deleteProduct(productId);
            }
        });
    });

    function deleteProduct(productId) {
        fetch(`/admin/products/${productId}/delete`, { // Assumindo uma rota de admin para deletar produtos
            method: 'POST', // Ou DELETE
            headers: {
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Produto excluído com sucesso!');
                window.location.reload();
            } else {
                alert('Erro ao excluir produto: ' + data.message);
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
