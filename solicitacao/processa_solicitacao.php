<?php
include '../includes/verifica_beneficiario.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location:" . BASE_URL . "login.php");
    exit();
}

// Verificar se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Iniciar transação
        $pdo->beginTransaction();

        // Inserir a solicitação na tabela Solicitacao
        $stmt = $pdo->prepare("INSERT INTO Solicitacao (id_beneficiario, justificativa, data_solicitacao) VALUES (:id_beneficiario, :justificativa, NOW())");
        $stmt->execute([
            ':id_beneficiario' => $_SESSION['usuario_id'],
            ':justificativa' => $_POST['justificativa'],
        ]);

        // Obter o id_solicitacao gerado
        $id_solicitacao = $pdo->lastInsertId();

        // Inserir dados na tabela Analise com status 'Em analise'
        $stmt = $pdo->prepare("INSERT INTO Analise (id_solicitacao, status_solicitacao) VALUES (:id_solicitacao, 'Em analise')");
        $stmt->execute([
            ':id_solicitacao' => $id_solicitacao
        ]);

        // Inserir as tintas solicitadas na tabela Solicitacao_tintas
        if (isset($_POST['tintas'])) {
            $stmt = $pdo->prepare("INSERT INTO Solicitacao_tintas (id_solicitacao, id_tintas, quantidade) VALUES (:id_solicitacao, :id_tintas, :quantidade)");
            foreach ($_POST['tintas'] as $id_tinta => $dados) {
                if (isset($dados['selecionada']) && isset($dados['quantidade']) && $dados['quantidade'] > 0) {
                    $stmt->execute([
                        ':id_solicitacao' => $id_solicitacao,
                        ':id_tintas' => $id_tinta,
                        ':quantidade' => $dados['quantidade'],
                    ]);
                }
            }
        }

        // Commit da transação
        $pdo->commit();
        // Redirecionar para a página inicial após sucesso
        echo "<p>Alterações salvas com sucesso! Você será redirecionado em 3 segundos.</p>";
        echo "<meta http-equiv='refresh' content='3;url=../index.php'>";
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "Erro ao atualizar os tipos de usuário: " . $e->getMessage();
    }
}