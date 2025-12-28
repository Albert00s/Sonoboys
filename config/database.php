<?php
// config/database.php

$host = "localhost";
$port = 3308;
$dbname = "igreja_sonoplastia";
$user = "root";
$pass = "063325";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        $options
    );
} catch (PDOException $e) {
    // Nunca mostrar erro real em produção
    die("Erro ao conectar ao banco de dados.");
}
