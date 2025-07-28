<?php

// Este é o arquivo de layout principal que todas as outras views irão incluir.
// Ele agora inclui os partials de header e footer para modularidade.
?>
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

    <?php
    // Inclui o partial do cabeçalho.
    // Ele contém a navbar e abre a div.container.
    include __DIR__ . '/partials/header.php';
    ?>

    <!-- O conteúdo específico de cada página será inserido aqui -->
    <?php
    if (isset($content)) {
        echo $content;
    }
    ?>

    <?php
    // Inclui o partial do rodapé.
    // Ele fecha a div.container, adiciona o footer e inclui os scripts JS.
    include __DIR__ . '/partials/footer.php';
    ?>
