<?php
require_once '../includes/config.php'; // Certifique-se de que isso inicializa $pdo
session_start();

include '../includes/gera_menu.php';

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $justificativa = isset($_POST['justificativa']) ? $_POST['justificativa'] : '';
    $tintas_selecionadas = isset($_POST['tintas']) ? $_POST['tintas'] : [];

    // Iniciar transação para garantir a integridade dos dados
    try {
        $pdo->beginTransaction();

        // Inserir a solicitação na tabela 'Solicitacao'
        $stmt = $pdo->prepare("INSERT INTO Solicitacao (id_beneficiario, data_solicitacao, justificativa) VALUES (?, NOW(), ?)");
        $stmt->execute([$usuario_id, $justificativa]);
        $solicitacao_id = $pdo->lastInsertId(); // Obter o ID da solicitação recém-criada

        // Inserir as tintas solicitadas na tabela 'Solicitacao_tintas'
        foreach ($tintas_selecionadas as $id_tinta => $dados) {
            if (isset($dados['selecionada']) && isset($dados['quantidade']) && $dados['quantidade'] > 0) {
                $stmt = $pdo->prepare("INSERT INTO Solicitacao_tintas (id_solicitacao, id_tintas, quantidade) VALUES (?, ?, ?)");
                $stmt->execute([$solicitacao_id, $id_tinta, $dados['quantidade']]);
            }
        }

        // Commit na transação
        $pdo->commit();

        // Redirecionar para a página inicial após sucesso
        header("Location: ../index.php?solicitacao_success=1");
        exit();
    } catch (Exception $e) {
        // Se ocorrer um erro, desfazemos a transação
        $pdo->rollBack();
        echo "Erro: " . $e->getMessage();
    }
}
?>
