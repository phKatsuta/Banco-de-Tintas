<?php
require_once '../includes/gera_menu.php';
// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location:" . BASE_URL . "login.php");
    exit();
}

// Obter o ID do usuário logado
$usuario_id = $_SESSION['usuario_id'];

// Verificar se o parâmetro id_solicitacao está presente na URL
if (!isset($_GET['id_solicitacao'])) {
    echo "Solicitação não encontrada.";
    exit();
}

$id_solicitacao = $_GET['id_solicitacao'];

// Consultar os detalhes da entrega relacionada à solicitação
$stmt = $pdo->prepare("
    SELECT 
        s.id_solicitacao,
        s.data_solicitacao,
        s.justificativa,
        a.status_solicitacao,
        e.id_entrega,
        e.dia_semana_entrega,
        e.horario_entrega,
        e.local_entrega,
        e.status_entrega
    FROM Solicitacao AS s
    INNER JOIN Analise AS a ON s.id_solicitacao = a.id_solicitacao
    LEFT JOIN Entrega AS e ON a.id_analise = e.id_analise
    WHERE s.id_solicitacao = :id_solicitacao AND s.id_beneficiario = :usuario_id AND s.excluido = 0
");
$stmt->execute(['id_solicitacao' => $id_solicitacao, 'usuario_id' => $usuario_id]);
$solicitacao_info = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$solicitacao_info) {
    echo "Não foi possível encontrar a solicitação ou você não tem permissão para visualizá-la.";
    exit();
}

include '../templates/header.php';
?>

<h1>Detalhes da Retirada de Tinta</h1>

<p><strong>Solicitação realizada em:</strong> <?php echo date('d/m/Y', strtotime($solicitacao_info['data_solicitacao'])); ?></p>
<p><strong>Justificativa:</strong> <?php echo htmlspecialchars($solicitacao_info['justificativa']); ?></p>
<p><strong>Status da Solicitação:</strong> <?php echo htmlspecialchars($solicitacao_info['status_solicitacao']); ?></p>

<?php if ($solicitacao_info['status_solicitacao'] === 'Aprovado' && $solicitacao_info['id_entrega']): ?>
    <h2>Detalhes da Retirada</h2>
    <p><strong>Status da Entrega:</strong> <?php echo htmlspecialchars($solicitacao_info['status_entrega']); ?></p>
    <p><strong>Local de Retirada:</strong> <?php echo htmlspecialchars($solicitacao_info['local_entrega']); ?></p>
    <p><strong>Dia da Semana para Retirada:</strong> 
        <?php
        $dias_semana = ["Domingo", "Segunda-feira", "Terça-feira", "Quarta-feira", "Quinta-feira", "Sexta-feira", "Sábado"];
        echo $dias_semana[$solicitacao_info['dia_semana_entrega']];
        ?>
    </p>
    <p><strong>Horário de Retirada:</strong> 
        <?php
        // Convertendo o horário (supondo que seja em formato de 24h)
        echo date('H:i', strtotime($solicitacao_info['horario_entrega']));
        ?>
    </p>
    <?php if ($solicitacao_info['status_entrega'] === 'Agendado'): ?>
        <p><strong>Por favor, compareça no local de retirada no horário agendado para retirar a tinta.</strong></p>
    <?php elseif ($solicitacao_info['status_entrega'] === 'Concluído'): ?>
        <p><strong>Retirada concluída. A tinta foi retirada com sucesso!</strong></p>
    <?php elseif ($solicitacao_info['status_entrega'] === 'Cancelado'): ?>
        <p><strong>A retirada foi cancelada. Entre em contato com o gestor para mais informações.</strong></p>
    <?php endif; ?>
<?php else: ?>
    <p><strong>A solicitação ainda não foi aprovada ou não há informações de entrega disponíveis.</strong></p>
<?php endif; ?>

<a href="minhas_solicitacoes.php" class="btn-return">Voltar para minhas solicitações</a>

<?php include '../templates/footer.php'; ?>
