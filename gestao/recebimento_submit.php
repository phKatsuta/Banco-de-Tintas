<?php
require_once '../includes/verifica_gestor.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter id_monitor e id_doacao
    $id_monitor = $_POST['id_monitor'] ?? null;
    $id_doacao = $_POST['id_doacao'] ?? null;

    // Verificar campos obrigatórios
    if (!$id_monitor || !$id_doacao) {
        die("Erro: Dados obrigatórios não identificados.");
    }

    // Verificar se as cores foram enviadas
    if (isset($_POST['cor_rgb']) && is_array($_POST['cor_rgb'])) {
        $cor_rgb = $_POST['cor_rgb'];

        // Processar cada tinta
        foreach ($cor_rgb as $id_tintas => $rgb) {
            // Atualizar a cor RGB
            $stmt_tintas = $pdo->prepare("UPDATE Tintas SET codigo_RGB = ? WHERE id_tintas = ?");
            $stmt_tintas->execute([$rgb, $id_tintas]);
        }

        // Atualizar o id_monitor na doação correspondente
        $stmt_doacao = $pdo->prepare("UPDATE Doacao SET id_monitor = ? WHERE id_doacao = ?");
        $stmt_doacao->execute([$id_monitor, $id_doacao]);

        $_SESSION['success_message'] = 'Recebimento da doação ID ' . $id_doacao . ' confirmado com sucesso!';
        header("Location: recebimento.php");
        exit();
    } else {
        $_SESSION['error_message'] = 'Nenhuma cor foi selecionada para confirmação.';
        header("Location: recebimento.php");
        exit();
    }
}
