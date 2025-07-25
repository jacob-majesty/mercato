<?php


// Define o título da página
$title = 'Página Inicial - Mercato';

// Inicia o buffer de saída para capturar o HTML desta view
ob_start();
?>

<h2>Produtos em Destaque</h2>
<div class="row row-cols-1 row-cols-md-3 g-4">
    <?php if (!empty($products)): ?>
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
    <?php else: ?>
        <div class="col-12">
            <p class="text-center fs-5 text-muted">Nenhum produto disponível no momento.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Links de Paginação (usando classes Bootstrap) -->
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

<?php
// Obtém o conteúdo do buffer e o passa para a variável $content
$content = ob_get_clean();

// Inclui o layout principal
require __DIR__ . '/layout/main.php';
?>
