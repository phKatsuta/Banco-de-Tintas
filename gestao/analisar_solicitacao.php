<?php
require_once '../includes/verifica_gestor.php';

// Consulta para obter as solicitações pendentes
$stmt_pendentes = $pdo->prepare("
SELECT 
    S.id_solicitacao,
    S.id_beneficiario,
    U.usuario_nome,
    S.data_solicitacao,
    S.justificativa AS justificativa_solicitacao,
    GROUP_CONCAT(CONCAT(T.nome_tintas, ' (', ST.quantidade, ')') SEPARATOR ', ') AS tintas_solicitadas,
    T.quantidade_tintas_disponivel,
    A.status_solicitacao
FROM 
    Solicitacao S
INNER JOIN Solicitacao_tintas ST ON S.id_solicitacao = ST.id_solicitacao
INNER JOIN Tintas T ON ST.id_tintas = T.id_tintas
INNER JOIN Analise A ON S.id_solicitacao = A.id_solicitacao
INNER JOIN usuarios U ON S.id_beneficiario = U.id
WHERE A.status_solicitacao = 'Em analise'
GROUP BY S.id_solicitacao;
");
$stmt_pendentes->execute();
$solicitacoes_pendentes = $stmt_pendentes->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obter as solicitações processadas
$stmt_processadas = $pdo->prepare("
SELECT 
    S.id_solicitacao,
    B.usuario_nome AS beneficiario_nome,
    S.data_solicitacao,
    A.status_solicitacao,
    A.data_analise,
    A.justificativa AS justificativa_analise,
    GROUP_CONCAT(T.nome_tintas SEPARATOR ', ') AS tintas_solicitadas
FROM 
    Solicitacao S
INNER JOIN Solicitacao_tintas ST ON S.id_solicitacao = ST.id_solicitacao
INNER JOIN Tintas T ON ST.id_tintas = T.id_tintas
INNER JOIN Usuarios B ON S.id_beneficiario = B.id
INNER JOIN Analise A ON S.id_solicitacao = A.id_solicitacao
GROUP BY S.id_solicitacao;
");
$stmt_processadas->execute();
$solicitacoes_processadas = $stmt_processadas->fetchAll(PDO::FETCH_ASSOC);

// Processamento do formulário de análise
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_solicitacao'])) {
    $id_solicitacao = filter_input(INPUT_POST, 'id_solicitacao', FILTER_VALIDATE_INT);
    $status_solicitacao = filter_input(INPUT_POST, 'status_solicitacao', FILTER_SANITIZE_STRING);
    $justificativa = filter_input(INPUT_POST, 'justificativa', FILTER_SANITIZE_STRING);
    $id_gestor = $_SESSION['usuario_id'];

    if ($id_solicitacao && $status_solicitacao && $justificativa) {
        $stmt_analise = $pdo->prepare("
            INSERT INTO Analise (id_gestor, id_solicitacao, status_solicitacao, data_analise, justificativa)
            VALUES (:id_gestor, :id_solicitacao, :status_solicitacao, NOW(), :justificativa)
        ");
        $stmt_analise->execute([
            ':id_gestor' => $id_gestor,
            ':id_solicitacao' => $id_solicitacao,
            ':status_solicitacao' => $status_solicitacao,
            ':justificativa' => $justificativa,
        ]);

        header('Location: analisar_solicitacoes.php');
        exit;
    } else {
        $error_message = "Por favor, preencha todos os campos corretamente.";
    }
}

include '../templates/header.php';
?>

<div class="container">
    <h1>Analisar Solicitações de Tintas</h1>

    <div class="tabs">
        <button class="tablinks active" onclick="openTab(event, 'Pendentes')">Pendentes</button>
        <button class="tablinks" onclick="openTab(event, 'Processadas')">Processadas</button>
    </div>

    <div id="Pendentes" class="tabcontent" style="display: block;">
        <h3>Solicitações Pendentes</h3>
        <?php if ($solicitacoes_pendentes): ?>
            <?php foreach ($solicitacoes_pendentes as $row): ?>
                <div class="solicitacao">
                    <p><strong>Beneficiário:</strong> <?= htmlspecialchars($row['beneficiario_nome']); ?></p>
                    <p><strong>Data:</strong> <?= date('d/m/Y', strtotime($row['data_solicitacao'])); ?></p>
                    <p><strong>Tintas Solicitadas:</strong> <?= htmlspecialchars($row['tintas_solicitadas']); ?></p>
                    <p><strong>Justificativa:</strong> <?= htmlspecialchars($row['justificativa_solicitacao']); ?></p>
                    
                    <form action="" method="POST">
                        <input type="hidden" name="id_solicitacao" value="<?= $row['id_solicitacao']; ?>">
                        <label for="status_solicitacao">Status:</label>
                        <select name="status_solicitacao" required>
                            <option value="Aprovado">Aprovado</option>
                            <option value="Parcialmente aprovado">Parcialmente aprovado</option>
                            <option value="Negado">Negado</option>
                        </select>
                        <label for="justificativa">Justificativa:</label>
                        <textarea name="justificativa" rows="3" required></textarea>
                        <button type="submit">Salvar</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Não há solicitações pendentes.</p>
        <?php endif; ?>
    </div>

    <div id="Processadas" class="tabcontent">
        <h3>Solicitações Processadas</h3>
        <?php if ($solicitacoes_processadas): ?>
            <?php foreach ($solicitacoes_processadas as $row): ?>
                <div class="solicitacao">
                    <p><strong>Beneficiário:</strong> <?= htmlspecialchars($row['beneficiario_nome']); ?></p>
                    <p><strong>Data:</strong> <?= date('d/m/Y', strtotime($row['data_solicitacao'])); ?></p>
                    <p><strong>Tintas Solicitadas:</strong> <?= htmlspecialchars($row['tintas_solicitadas']); ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($row['status_solicitacao']); ?></p>
                    <p><strong>Data da Análise:</strong> <?= date('d/m/Y', strtotime($row['data_analise'])); ?></p>
                    <p><strong>Justificativa da Análise:</strong> <?= htmlspecialchars($row['justificativa_analise']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Não há solicitações processadas.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    function openTab(evt, tabName) {
        let tabcontent = document.querySelectorAll('.tabcontent');
        tabcontent.forEach(content => content.style.display = 'none');

        let tablinks = document.querySelectorAll('.tablinks');
        tablinks.forEach(link => link.className = link.className.replace(' active', ''));

        document.getElementById(tabName).style.display = 'block';
        evt.currentTarget.className += ' active';
    }
</script>

<?php include '../templates/footer.php'; ?>
