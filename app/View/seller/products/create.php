<?php

// Define o título da página
$title = 'Criar Novo Produto - Mercato';

// Inicia o buffer de saída para capturar o HTML desta view
ob_start();
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white text-center">
                <h4 class="mb-0"><i class="fas fa-plus-circle"></i> Cadastrar Novo Produto</h4>
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

                <form id="createProductForm" action="/seller/products" method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome do Produto</label>
                        <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Preço (R$)</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" required min="0.01" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Categoria</label>
                        <input type="text" class="form-control" id="category" name="category" required value="<?= htmlspecialchars($_POST['category'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="imageUrl" class="form-label">URL da Imagem</label>
                        <input type="url" class="form-control" id="imageUrl" name="image_url" placeholder="/products/books/exemplo.jpg" value="<?= htmlspecialchars($_POST['image_url'] ?? '') ?>">
                        <div class="form-text">Caminho relativo à pasta `public/`, ex: `/products/books/meu-livro.jpg`</div>
                    </div>
                    <div class="mb-3">
                        <label for="stock" class="form-label">Estoque</label>
                        <input type="number" class="form-control" id="stock" name="stock" required min="0" value="<?= htmlspecialchars($_POST['stock'] ?? '') ?>">
                    </div>
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save"></i> Salvar Produto</button>
                        <a href="/seller/products" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar para Meus Produtos</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createProductForm');
    form.addEventListener('submit', function(event) {
        event.preventDefault(); // Previne o envio padrão do formulário

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                // Adicione CSRF token se estiver usando
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            const messageDiv = document.getElementById('message'); // Crie uma div para mensagens
            if (result.success) {
                alert(result.message);
                window.location.href = '/seller/products'; // Redireciona após sucesso
            } else {
                alert('Erro: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Ocorreu um erro ao comunicar com o servidor.');
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
