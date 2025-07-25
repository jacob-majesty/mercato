<?php

// Define o título da página
$title = 'Detalhes do Usuário: ' . htmlspecialchars($user->getFirstName() . ' ' . $user->getLastName()) . ' - Mercato';

// Inicia o buffer de saída para capturar o HTML desta view
ob_start();
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-danger text-white text-center">
                <h4 class="mb-0"><i class="fas fa-user"></i> Detalhes do Usuário</h4>
            </div>
            <div class="card-body p-4">
                <?php if (isset($error) && !empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($user) && $user !== null): ?>
                    <p><strong>ID:</strong> <?= htmlspecialchars($user->getId()) ?></p>
                    <p><strong>Nome Completo:</strong> <?= htmlspecialchars($user->getFirstName() . ' ' . $user->getLastName()) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user->getEmail()) ?></p>
                    <p><strong>Papel:</strong> 
                        <span class="badge 
                            <?php
                            switch ($user->getRole()) {
                                case 'admin': echo 'bg-danger'; break;
                                case 'seller': echo 'bg-info'; break;
                                case 'client': echo 'bg-primary'; break;
                                default: echo 'bg-secondary'; break;
                            }
                            ?>">
                            <?= htmlspecialchars(ucfirst($user->getRole())) ?>
                        </span>
                    </p>
                    <p><strong>Criado Em:</strong> <?= htmlspecialchars($user->getCreatedAt()->format('d/m/Y H:i:s')) ?></p>
                    
                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="/admin/users" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar para Usuários</a>
                        <!-- Adicionar botão de edição se houver rota de edição de usuário -->
                        <!-- <a href="/admin/users/<?= htmlspecialchars($user->getId()) ?>/edit" class="btn btn-warning text-white"><i class="fas fa-edit"></i> Editar Usuário</a> -->
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning text-center" role="alert">
                        Usuário não encontrado.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Obtém o conteúdo do buffer e o passa para a variável $content
$content = ob_get_clean();

// Inclui o layout principal
require __DIR__ . '/../layout/main.php';
?>
