<?php
require_once '../includes/verifica_gestor.php';

// Verificar se os dados foram enviados corretamente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter os dados do formulário
    $id_solicitacao = $_POST['id_solicitacao'];
    $id_analise = $_POST['id_analise'];
    $status_solicitacao = $_POST['status_solicitacao'];
    $justificativa_analise = isset($_POST['justificativa_analise']) ? $_POST['justificativa_analise'] : '';

    // Validar os dados
    if (empty($status_solicitacao)) {
        echo "Por favor, selecione o status da solicitação.";
        exit();
    }

    try {
        // Atualizar o status da solicitação na tabela 'Analise'
        $stmt = $pdo->prepare("
            UPDATE Analise 
            SET status_solicitacao = :status_solicitacao,
                justificativa = :justificativa_analise,
                data_analise = NOW()
            WHERE id_analise = :id_analise
        ");
        $stmt->execute([
            ':status_solicitacao' => $status_solicitacao,
            ':justificativa_analise' => $justificativa_analise,
            ':id_analise' => $id_analise
        ]);

        // Se a solicitação foi aprovada ou parcialmente aprovada, pode ser necessário agendar a entrega
        if ($status_solicitacao == 'Aprovado' || $status_solicitacao == 'Parcialmente aprovado') {
            // Criar o agendamento de entrega ou marcar como pendente para o beneficiário
            // Aqui você pode redirecionar o Gestor para a página de agendamento de entrega
            // Ou, se necessário, registrar mais informações na tabela 'Entrega'
            // Por enquanto, vamos apenas informar que a solicitação foi analisada
            // O processo de entrega será feito em outra parte do sistema.
        }

        // Redirecionar de volta para a página de análise de solicitações
        header("Location: analisar_solicitacao.php");
        exit();

    } catch (Exception $e) {
        echo "Erro ao processar a análise: " . $e->getMessage();
        exit();
    }
} else {
    // Caso a requisição não seja POST, redireciona para a página de análise
    header("Location: analisar_solicitacao.php");
    exit();
}
