<?php
require_once '../includes/verifica_gestor.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

include '../templates/header.php';

// Consulta para obter as doações a confirmar
$queryAConfirmar = "
    SELECT 
        d.id_doacao, d.data_doacao, d.local_doado,
        t.id_tintas, t.nome_tintas, t.marca, t.linha, t.acabamento,
        dt.quantidade_tintas_doada, t.data_validade_tintas
    FROM Doacao d
    INNER JOIN Doacao_tintas dt ON d.id_doacao = dt.id_doacao
    INNER JOIN Tintas t ON dt.id_tintas = t.id_tintas
    WHERE t.codigo_RGB IS NULL
    ORDER BY d.data_doacao DESC
";
$stmtAConfirmar = $pdo->query($queryAConfirmar);
$doacoesAConfirmar = $stmtAConfirmar->fetchAll(PDO::FETCH_GROUP);

// Consulta para obter as doações confirmadas
$queryConfirmadas = "
    SELECT 
        d.id_doacao, d.data_doacao, d.local_doado,
        t.id_tintas, t.nome_tintas, t.marca, t.linha, t.acabamento,
        dt.quantidade_tintas_doada, t.data_validade_tintas, t.codigo_RGB
    FROM Doacao d
    INNER JOIN Doacao_tintas dt ON d.id_doacao = dt.id_doacao
    INNER JOIN Tintas t ON dt.id_tintas = t.id_tintas
    WHERE t.codigo_RGB IS NOT NULL
    ORDER BY d.data_doacao DESC
";
$stmtConfirmadas = $pdo->query($queryConfirmadas);
$doacoesConfirmadas = $stmtConfirmadas->fetchAll(PDO::FETCH_GROUP);

?>

<h2>Doações Realizadas</h2>

<!-- Doações a Confirmar -->
<details>
    <Summary>A CONFIRMAR</Summary>
    <?php if (!empty($doacoesAConfirmar)): ?>
        <?php foreach ($doacoesAConfirmar as $idDoacao => $tintas): ?>
            <details>
                <summary>
                    Doação ID: <?= $idDoacao ?> | Data: <?= date('d/m/Y', strtotime($tintas[0]['data_doacao'])) ?> | Local:
                    <?= htmlspecialchars($tintas[0]['local_doado']) ?>
                </summary>
                <ul>
                    <?php foreach ($tintas as $tinta): ?>
                        <li>
                            <strong>Tinta:</strong> <?= htmlspecialchars($tinta['nome_tintas']) ?><br>
                            <strong>Marca:</strong> <?= htmlspecialchars($tinta['marca']) ?><br>
                            <strong>Linha:</strong> <?= htmlspecialchars($tinta['linha']) ?><br>
                            <strong>Acabamento:</strong> <?= htmlspecialchars($tinta['acabamento']) ?><br>
                            <strong>Quantidade:</strong> <?= $tinta['quantidade_tintas_doada'] ?> L<br>
                            <strong>Validade:</strong> <?= date('d/m/Y', strtotime($tinta['data_validade_tintas'])) ?>
                        </li>
                        <hr>
                    <?php endforeach; ?>
                </ul>
            </details>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Não há doações a confirmar.</p>
    <?php endif; ?>
</details>
<!-- Doações Confirmadas -->
<details>
    <summary>CONFIRMADAS</summary>
    <h3>Doações Confirmadas</h3>
    <?php if (!empty($doacoesConfirmadas)): ?>
        <?php foreach ($doacoesConfirmadas as $idDoacao => $tintas): ?>
            <details>
                <summary>
                    Doação ID: <?= $idDoacao ?> | Data: <?= date('d/m/Y', strtotime($tintas[0]['data_doacao'])) ?> | Local:
                    <?= htmlspecialchars($tintas[0]['local_doado']) ?>
                </summary>
                <ul>
                    <?php foreach ($tintas as $tinta): ?>
                        <li>
                            <strong>Tinta:</strong> <?= htmlspecialchars($tinta['nome_tintas']) ?><br>
                            <strong>Marca:</strong> <?= htmlspecialchars($tinta['marca']) ?><br>
                            <strong>Linha:</strong> <?= htmlspecialchars($tinta['linha']) ?><br>
                            <strong>Acabamento:</strong> <?= htmlspecialchars($tinta['acabamento']) ?><br>
                            <strong>Quantidade:</strong> <?= $tinta['quantidade_tintas_doada'] ?> L<br>
                            <strong>Validade:</strong> <?= date('d/m/Y', strtotime($tinta['data_validade_tintas'])) ?><br>
                            <strong>Cor:</strong>
                            <canvas width="20" height="20"
                                style="border: 1px solid #000; background-color: <?= htmlspecialchars($tinta['codigo_RGB']) ?>;"></canvas>
                        </li>
                        <hr>
                    <?php endforeach; ?>
                </ul>
            </details>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Não há doações confirmadas.</p>
    <?php endif; ?>
</details>
<script src="../SCRIPT/script.js"></script>
<?php include '../templates/footer.php'; ?>