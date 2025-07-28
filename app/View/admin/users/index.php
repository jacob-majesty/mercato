<?php

// Define o título da página
$title = 'Gestão de Usuários - Mercato';

// Inicia o buffer de saída para capturar o HTML desta view
ob_start();
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-users-cog"></i> Gestão de Usuários</h4>
                <!-- Adicionar link para criar novo usuário se houver rota -->
                <!-- <a href="/admin/users/create" class="btn btn-success btn-sm"><i class="fas fa-user-plus"></i> Novo Usuário</a> -->
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

                <?php if (!empty($users)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Nome Completo</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Papel</th>
                                    <th scope="col">Criado Em</th>
                                    <th scope="col" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user->getId()) ?></td>
                                        <td><?= htmlspecialchars($user->getFirstName() . ' ' . $user->getLastName()) ?></td>
                                        <td><?= htmlspecialchars($user->getEmail()) ?></td>
                                        <td>
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
                                        </td>
                                        <td><?= htmlspecialchars($user->getCreatedAt()->format('d/m/Y H:i:s')) ?></td>
                                        <td class="text-center">
                                            <a href="/admin/users/<?= htmlspecialchars($user->getId()) ?>" class="btn btn-info btn-sm me-1" title="Ver Detalhes">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <!-- Botão de exclusão (requer JS para AJAX) -->
                                            <?php if (\App\Core\Authenticator::getUserId() !== $user->getId()): // Não permitir que o admin delete a si mesmo ?>
                                                <button class="btn btn-danger btn-sm delete-user-btn" data-user-id="<?= htmlspecialchars($user->getId()) ?>" title="Excluir">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-secondary btn-sm" disabled title="Você não pode se auto-excluir">
                                                    <i class="fas fa-trash-alt"></i>
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
                        Nenhum usuário cadastrado no sistema.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-user-btn').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            if (confirm('Tem certeza que deseja excluir este usuário? Esta ação é irreversível.')) {
                deleteUser(userId);
            }
        });
    });

    function deleteUser(userId) {
        fetch(`/admin/users/${userId}/delete`, {
            method: 'POST', // Ou DELETE, dependendo da sua rota RESTful
            headers: {
                'Content-Type': 'application/json',
                // Adicione CSRF token se estiver usando
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Usuário excluído com sucesso!');
                window.location.reload(); // Recarrega a página para atualizar a lista
            } else {
                alert('Erro ao excluir usuário: ' + data.message);
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
require __DIR__ . '/../../layout/main.php';
?>
