<?php
require_once '../includes/verifica_gestor.php';

// Verificar se o botão de confirmação foi pressionado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter id_monitor
    $id_monitor = $_POST['id_monitor'] ?? null;

    // Verificar se o id_monitor está presente
    if (!$id_monitor) {
        die("Erro: Monitor não identificado.");
    }

    // Verificar se as cores foram enviadas
    if (isset($_POST['cor_rgb']) && is_array($_POST['cor_rgb'])) {
        $cor_rgb = $_POST['cor_rgb'];

        // Processar cada doação
        foreach ($cor_rgb as $id_tintas => $rgb) {
            // Verificar se a cor não está vazia
            if (!empty($rgb)) {
                // Atualizar a cor RGB para a tinta correspondente
                $stmt_tintas = $pdo->prepare("UPDATE Tintas SET codigo_RGB = ? WHERE id_tintas = ?");
                $stmt_tintas->execute([$rgb, $id_tintas]);
            }
        }

        // Atualizar o id_monitor na tabela Doacao para associar a confirmação ao monitor
        $stmt_doacao = $pdo->prepare("UPDATE Doacao SET id_monitor = ? WHERE id_doacao IN (SELECT id_doacao FROM Doacao_tintas WHERE id_tintas IN (" . implode(',', array_keys($cor_rgb)) . "))");
        $stmt_doacao->execute([$id_monitor]);

        $_SESSION['success_message'] = 'Recebimento confirmado com sucesso!';
        header("Location: recebimento.php");
        exit();
    } else {
        // Caso não haja cores para confirmar
        $_SESSION['error_message'] = 'Nenhuma cor foi selecionada para confirmação.';
        header("Location: recebimento.php");
        exit();
    }
}
