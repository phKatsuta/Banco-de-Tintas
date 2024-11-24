<?php
session_start();

require 'includes\auth.php';
check_access('Gestor');

if (!isset($_SESSION['usuario_id']) || !in_array('Gestor', $_SESSION['user_types'])) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Gestor</title>
</head>
<body>
    <h1>Bem-vindo, Gestor!</h1>
    <ul>
        <li><a href="cadastro_monitores.php">Cadastro de Monitores</a></li>
        <li><a href="cadastro_doacoes.php">Cadastro de Doações</a></li>
        <!-- Outros links -->
    </ul>
</body>
</html>
