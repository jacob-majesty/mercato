<?php

// Define o título da página
$title = htmlspecialchars($product->getName()) . ' - Mercato';

// Inicia o buffer de saída para capturar o HTML desta view
ob_start();
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-10 col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0"><i class="fas fa-info-circle"></i> Detalhes do Produto</h4>
            </div>
            <div class="card-body p-4">
                <?php if (isset($error) && !empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($product) && $product !== null): ?>
                    <div class="row">
                        <div class="col-md-5 text-center">
                            <?php
                                $imageSrc = !empty($product->getImageUrl()) ? htmlspecialchars($product->getImageUrl()) : htmlspecialchars($placeholderImagePath);
                            ?>
                            <img src="<?= $imageSrc ?>" class="img-fluid rounded shadow-sm" alt="Imagem do Produto: <?= htmlspecialchars($product->getName()) ?>" style="max-height: 400px; object-fit: contain;">
                        </div>
                        <div class="col-md-7">
                            <h2><?= htmlspecialchars($product->getName()) ?></h2>
                            <p class="lead text-primary fw-bold fs-3">R$ <?= number_format($product->getPrice(), 2, ',', '.') ?></p>
                            <p><strong>Categoria:</strong> <?= htmlspecialchars($product->getCategory()) ?></p>
                            <p><strong>Descrição:</strong></p>
                            <p><?= nl2br(htmlspecialchars($product->getDescription())) ?></p>
                            <p><strong>Estoque Disponível:</strong> 
                                <?php if ($product->getStock() > 0): ?>
                                    <span class="badge bg-success fs-6"><?= htmlspecialchars($product->getStock()) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-danger fs-6">Esgotado</span>
                                <?php endif; ?>
                            </p>

                            <?php if ($product->getStock() > 0): ?>
                                <div class="d-flex align-items-center mb-3">
                                    <label for="quantity" class="form-label me-2 mb-0">Quantidade:</label>
                                    <input type="number" class="form-control w-25" id="quantity" value="1" min="1" max="<?= htmlspecialchars($product->getStock()) ?>">
                                </div>
                                <button class="btn btn-success btn-lg add-to-cart-btn" data-product-id="<?= htmlspecialchars($product->getId()) ?>">
                                    <i class="fas fa-cart-plus"></i> Adicionar ao Carrinho
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-lg" disabled>Produto Esgotado</button>
                            <?php endif; ?>

                            <hr class="my-4">
                            <a href="/products" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Voltar para a lista de produtos</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning text-center" role="alert">
                        Produto não encontrado.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addToCartBtn = document.querySelector('.add-to-cart-btn');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const quantityInput = document.getElementById('quantity');
            const quantity = parseInt(quantityInput.value);

            if (isNaN(quantity) || quantity <= 0) {
                alert('Por favor, insira uma quantidade válida.');
                return;
            }

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
                    // Opcional: Redirecionar para o carrinho ou atualizar um ícone
                } else {
                    alert('Erro ao adicionar ao carrinho: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Ocorreu um erro ao comunicar com o servidor.');
            });
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
