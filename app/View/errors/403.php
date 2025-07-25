<?php

// Define o título da página
$title = 'Acesso Negado - Mercato';

// Inicia o buffer de saída para capturar o HTML desta view
ob_start();
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-8 text-center">
        <div class="card shadow-sm p-4">
            <h1 class="display-1 text-danger"><i class="fas fa-ban"></i> 403</h1>
            <h2 class="mb-3">Acesso Negado</h2>
            <p class="lead">Você não tem permissão para acessar esta página ou recurso.</p>
            <p class="mb-4"><?= htmlspecialchars($message ?? 'Por favor, verifique suas credenciais ou entre em contato com o suporte.') ?></p>
            <a href="/" class="btn btn-primary btn-lg"><i class="fas fa-home"></i> Voltar para a Página Inicial</a>
            <?php if (!\App\Core\Authenticator::check()): ?>
                <a href="/login" class="btn btn-secondary btn-lg ms-2"><i class="fas fa-sign-in-alt"></i> Fazer Login</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Obtém o conteúdo do buffer e o passa para a variável $content
$content = ob_get_clean();

// Inclui o layout principal
require __DIR__ . '/../layout/main.php';
?>
