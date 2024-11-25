<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$user_types = $_SESSION['user_types'] ?? [];

if (in_array('Gestor', $user_types)) {
    header("Location: gestao/dashboard.php");
    exit;
} elseif (in_array('Monitor', $user_types)) {
    header("Location: monitor/dashboard.php");
    exit;
} elseif (in_array('Doador', $user_types)) {
    header("Location: doacao/doacao.php");
    exit;
} elseif (in_array('Beneficiario', $user_types)) {
    header("Location: beneficiario/dashboard.php");
    exit;
} else {
    echo "Tipo de usuário não reconhecido.";
}
?>
