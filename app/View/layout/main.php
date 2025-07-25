<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Mercato' ?></title>

    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Seu CSS personalizado -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- Opcional: Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">Mercato</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/products">Produtos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/cart">Carrinho</a>
                    </li>
                    <?php if (\App\Core\Authenticator::check()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/orders">Meus Pedidos</a>
                        </li>
                        <?php if (\App\Core\Authenticator::isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/admin/dashboard">Admin Dashboard</a>
                            </li>
                        <?php elseif (\App\Core\Authenticator::isSeller()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/seller/dashboard">Vendedor Dashboard</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (\App\Core\Authenticator::check()): ?>
                        <li class="nav-item">
                            <span class="nav-link text-white">Olá, <?= htmlspecialchars(\App\Core\Authenticator::user()->getFirstName() ?? 'Usuário') ?>!</span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-light ms-2" href="/logout">Sair</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-light" href="/login">Entrar</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-light ms-2" href="/register">Registrar</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php
        // Conteúdo específico de cada página será inserido aqui
        // Isso é um placeholder para o conteúdo que vem de outras views
        if (isset($content)) {
            echo $content;
        }
        ?>
    </div>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> Mercato. Todos os direitos reservados.</p>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Seu JavaScript personalizado -->
    <script src="/assets/js/script.js"></script>
</body>
</html>
