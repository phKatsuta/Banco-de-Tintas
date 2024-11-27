<?php
require_once '../includes/gera_menu.php';
// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location:" . BASE_URL . "login.php");
    exit();
}

// Função para obter os tipos de usuário
function getUserTypes($pdo, $usuario_id)
{
    $sql = "SELECT tipo FROM Usuario_Tipos WHERE usuario_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);

    $tipos = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tipos[] = $row['tipo'];
    }
    return $tipos;
}

// Verifica se o usuário é do tipo Doador
$tipos_usuario = getUserTypes($pdo, $_SESSION['usuario_id']);
if (!in_array('Beneficiario', $tipos_usuario)) {
    header("Location:" . BASE_URL . "login.php");
    exit();
}
