<?php
require_once '../includes/verifica_gestor.php';  // Verifica se o usuário tem permissões de gestor

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obter os dados do formulário
    $id_solicitacao = $_POST['id_solicitacao'];
    $status_analise = $_POST['status_analise'];
    $justificativa_analise = $_POST['justificativa_analise'];

    // O ID do gestor é o ID do usuário logado
    $id_gestor = $_SESSION['id'];  // Supondo que o ID do usuário logado seja armazenado na sessão

    try {
        // Iniciar uma transação
        $pdo->beginTransaction();

        // Verifica se já existe uma entrada na tabela 'Analise' para essa solicitação
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM Analise WHERE id_solicitacao = :id_solicitacao");
        $stmt_check->bindParam(':id_solicitacao', $id_solicitacao);
        $stmt_check->execute();
        $exists = $stmt_check->fetchColumn();

        // Se já existe uma análise, atualiza; caso contrário, insere uma nova entrada
        if ($exists) {
            // Atualiza o status e a justificativa na tabela 'Analise'
            $stmt = $pdo->prepare("
                UPDATE Analise
                SET 
                    id_gestor = :id_gestor,
                    status_solicitacao = :status_analise,
                    data_analise = NOW()
                    justificativa = :justificativa_analise,
                WHERE id_solicitacao = :id_solicitacao
            ");
            $stmt->bindParam(':id_gestor', $id_gestor);
            $stmt->bindParam(':id_solicitacao', $id_solicitacao);
            $stmt->bindParam(':status_analise', $status_analise);
            $stmt->bindParam(':justificativa_analise', $justificativa_analise);
            $stmt->execute();
        } else {
            // Se não existe, insere uma nova análise para a solicitação
            $stmt = $pdo->prepare("
                INSERT INTO Analise (id_solicitacao, status_solicitacao, justificativa, id_gestor, data_analise)
                VALUES (:id_solicitacao, :status_analise, :justificativa_analise, :id_gestor, NOW())
            ");
            $stmt->bindParam(':id_gestor', $id_gestor);
            $stmt->bindParam(':id_solicitacao', $id_solicitacao);
            $stmt->bindParam(':status_analise', $status_analise);
            $stmt->bindParam(':justificativa_analise', $justificativa_analise);
            $stmt->execute();
        }

        // Confirma a transação
        $pdo->commit();

        // Redireciona de volta para a página de análise com uma mensagem de sucesso
        header('Location: analisar_solicitacao.php?success=1');
        exit;

    } catch (Exception $e) {
        // Se ocorrer um erro, reverter a transação
        $pdo->rollBack();
        // Exibir a mensagem de erro
        echo 'Erro: ' . $e->getMessage();
    }
} else {
    // Se o formulário não foi enviado corretamente, redireciona de volta
    header('Location: analisar_solicitacao.php?error=1');
    exit;
}
