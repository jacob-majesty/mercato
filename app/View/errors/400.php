<?php


// Define o título da página
$title = 'Requisição Inválida - Mercato';

// Inicia o buffer de saída para capturar o HTML desta view
ob_start();
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-8 text-center">
        <div class="card shadow-sm p-4">
            <h1 class="display-1 text-warning"><i class="fas fa-exclamation-triangle"></i> 400</h1>
            <h2 class="mb-3">Requisição Inválida</h2>
            <p class="lead">O servidor não conseguiu entender a requisição devido a sintaxe inválida.</p>
            <p class="mb-4"><?= htmlspecialchars($message ?? 'Por favor, verifique sua entrada e tente novamente.') ?></p>
            <a href="/" class="btn btn-primary btn-lg"><i class="fas fa-home"></i> Voltar para a Página Inicial</a>
        </div>
    </div>
</div>

<?php
// Obtém o conteúdo do buffer e o passa para a variável $content
$content = ob_get_clean();

// Inclui o layout principal
require __DIR__ . '/../layout/main.php';
?>
