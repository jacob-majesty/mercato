<?php

// Define o título da página
$title = 'Todos os Produtos - Mercato';

// Inicia o buffer de saída para capturar o HTML desta view
ob_start();
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0"><i class="fas fa-box"></i> Nossos Produtos</h4>
            </div>
            <div class="card-body p-4">
                <?php if (isset($error) && !empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($products)): ?>
                    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
                        <?php foreach ($products as $product): ?>
                            <div class="col">
                                <div class="card h-100">
                                    <?php
                                        // Usa a URL da imagem do produto ou a imagem de placeholder
                                        $imageSrc = !empty($product->getImageUrl()) ? htmlspecialchars($product->getImageUrl()) : htmlspecialchars($placeholderImagePath);
                                    ?>
                                    <img src="<?= $imageSrc ?>" class="card-img-top p-3" alt="Imagem do Produto: <?= htmlspecialchars($product->getName()) ?>" style="height: 200px; object-fit: contain;">
                                    <div class="card-body text-center">
                                        <h5 class="card-title"><?= htmlspecialchars($product->getName()) ?></h5>
                                        <p class="card-text text-primary fw-bold">R$ <?= number_format($product->getPrice(), 2, ',', '.') ?></p>
                                        <p class="card-text text-muted">Estoque: <?= htmlspecialchars($product->getStock()) ?></p>
                                        <a href="/products/<?= htmlspecialchars($product->getId()) ?>" class="btn btn-primary btn-sm">Ver Detalhes</a>
                                        <button class="btn btn-success btn-sm mt-2 add-to-cart-btn" data-product-id="<?= htmlspecialchars($product->getId()) ?>">
                                            <i class="fas fa-cart-plus"></i> Adicionar ao Carrinho
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Links de Paginação -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Paginação de Produtos" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $currentPage - 1 ?>" tabindex="-1" aria-disabled="<?= ($currentPage <= 1) ? 'true' : 'false' ?>">Anterior</a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $currentPage + 1 ?>">Próxima</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="alert alert-info text-center" role="alert">
                        Nenhum produto disponível no momento.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            // Aqui você pode adicionar uma lógica para obter a quantidade, por exemplo, 1
            const quantity = 1; 

            fetch('/cart/add', { // Rota para adicionar ao carrinho
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ product_id: productId, quantity: quantity })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Produto adicionado ao carrinho com sucesso!');
                    // Opcional: Atualizar um contador de carrinho na UI
                } else {
                    alert('Erro ao adicionar ao carrinho: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Ocorreu um erro ao comunicar com o servidor.');
            });
        });
    });
});
</script>

<?php
// Obtém o conteúdo do buffer e o passa para a variável $content
$content = ob_get_clean();

// Inclui o layout principal
require __DIR__ . '/../layout/main.php';
?>
