<?php
// Dados de conexão
$host = "localhost";
$username = "root";
$password = "";
$dbname = "banco_de_tintas_5";

try {
    // Cria uma nova instância de PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Define o modo de erro do PDO para exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage();
    die();
}