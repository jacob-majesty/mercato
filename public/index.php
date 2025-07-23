<?php

require __DIR__ . '/../vendor/autoload.php';


echo "<h1>Mercato App Rodando no Docker! 🎉</h1>";
echo "<p>PHP Versão: " . phpversion() . "</p>";

// Exemplo de conexão com o MySQL
$servername = "mysql"; // Nome do serviço MySQL no docker-compose.yml
$username = getenv('MYSQL_USER') ?: "user"; // Usar variáveis de ambiente ou default
$password = getenv('MYSQL_PASSWORD') ?: "user_password";
$dbname = getenv('MYSQL_DATABASE') ?: "mercato_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>Conexão com o MySQL (<code>$dbname</code>) estabelecida com sucesso! ✅</p>";

    // Exemplo: Buscar dados (se você já tiver dados e schema.sql/seed.sql importados)
    // $stmt = $conn->query("SELECT * FROM products LIMIT 5");
    // $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // if (!empty($products)) {
    //     echo "<h2>Exemplo de Produtos:</h2>";
    //     echo "<pre>" . htmlspecialchars(json_encode($products, JSON_PRETTY_PRINT)) . "</pre>";
    // }

} catch (PDOException $e) {
    echo "<p style='color: red;'>Falha na conexão com o MySQL: " . htmlspecialchars($e->getMessage()) . " ❌</p>";
}

