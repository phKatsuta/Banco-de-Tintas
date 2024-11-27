<?php
require_once '../includes/verifica_gestor.php';
// Consultar todas as solicitações pendentes de análise
$stmt = $pdo->prepare("
    SELECT 
        s.id_solicitacao, 
        s.id_beneficiario, 
        s.data_solicitacao, 
        s.justificativa, 
        a.status_solicitacao,
        a.id_analise,
        a.justificativa AS justificativa_analise
    FROM Solicitacao AS s
    LEFT JOIN Analise AS a ON s.id_solicitacao = a.id_solicitacao
    WHERE a.status_solicitacao = 'Em analise' OR a.status_solicitacao IS NULL
");
$stmt->execute();
$solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$solicitacoes) {
    echo "Não há solicitações pendentes para análise.";
    exit();
}

include '../templates/header.php';
?>

<h1>Analisar Solicitações de Retirada de Tinta</h1>

<?php foreach ($solicitacoes as $solicitacao): ?>
    <div class="solicitacao">
        <h2>Solicitação #<?php echo $solicitacao['id_solicitacao']; ?></h2>
        <p><strong>Beneficiário ID:</strong> <?php echo $solicitacao['id_beneficiario']; ?></p>
        <p><strong>Data da Solicitação:</strong> <?php echo date('d/m/Y', strtotime($solicitacao['data_solicitacao'])); ?></p>
        <p><strong>Justificativa:</strong> <?php echo htmlspecialchars($solicitacao['justificativa']); ?></p>

        <?php
        // Consultar as tintas solicitadas
        $stmt_tintas = $pdo->prepare("
            SELECT 
                t.nome_tinta, 
                st.quantidade
            FROM Solicitacao_tintas AS st
            INNER JOIN Tintas AS t ON st.id_tintas = t.id_tintas
            WHERE st.id_solicitacao = :id_solicitacao
        ");
        $stmt_tintas->execute(['id_solicitacao' => $solicitacao['id_solicitacao']]);
        $tintas = $stmt_tintas->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <h3>Tintas Solicitadas</h3>
        <ul>
            <?php foreach ($tintas as $tinta): ?>
                <li><?php echo htmlspecialchars($tinta['nome_tinta']); ?> - Quantidade: <?php echo $tinta['quantidade']; ?> L</li>
            <?php endforeach; ?>
        </ul>

        <h3>Status da Análise</h3>
        <form action="analisar_solicitacao_submit.php" method="POST">
            <input type="hidden" name="id_solicitacao" value="<?php echo $solicitacao['id_solicitacao']; ?>">
            <input type="hidden" name="id_analise" value="<?php echo $solicitacao['id_analise']; ?>">
            
            <label for="status_solicitacao">Status da Solicitação:</label>
            <select name="status_solicitacao" id="status_solicitacao" required>
                <option value="Aprovado" <?php echo ($solicitacao['status_solicitacao'] == 'Aprovado') ? 'selected' : ''; ?>>Aprovado</option>
                <option value="Parcialmente aprovado" <?php echo ($solicitacao['status_solicitacao'] == 'Parcialmente aprovado') ? 'selected' : ''; ?>>Parcialmente aprovado</option>
                <option value="Negado" <?php echo ($solicitacao['status_solicitacao'] == 'Negado') ? 'selected' : ''; ?>>Negado</option>
            </select>
            <br>

            <label for="justificativa_analise">Justificativa:</label>
            <textarea name="justificativa_analise" id="justificativa_analise" rows="4" cols="50" placeholder="Caso a solicitação seja negada ou parcialmente aprovada, insira uma justificativa."><?php echo htmlspecialchars($solicitacao['justificativa_analise']); ?></textarea>
            <br>

            <button type="submit">Registrar Análise</button>
        </form>
    </div>
    <hr>
<?php endforeach; ?>

<?php include '../templates/footer.php'; ?>
