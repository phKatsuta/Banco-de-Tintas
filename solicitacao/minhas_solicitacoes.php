<?php
require_once '../includes/verifica_beneficiario.php';
// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location:" . BASE_URL . "login.php");
    exit();
}

// Obter o ID do usuário logado
$usuario_id = $_SESSION['usuario_id'];

// Consultar solicitações realizadas pelo usuário
$stmt = $pdo->prepare("
    SELECT 
        s.id_solicitacao,
        s.data_solicitacao,
        s.justificativa,
        t.nome_tintas,
        t.marca,
        t.linha,
        t.acabamento,
        st.quantidade,
        t.data_validade_tintas,
        t.codigo_RGB,
        a.status_solicitacao,
        e.id_entrega
    FROM Solicitacao AS s
    INNER JOIN Solicitacao_tintas AS st ON s.id_solicitacao = st.id_solicitacao
    INNER JOIN Tintas AS t ON st.id_tintas = t.id_tintas
    LEFT JOIN Analise AS a ON s.id_solicitacao = a.id_solicitacao
    LEFT JOIN Entrega AS e ON a.id_analise = e.id_analise
    WHERE s.id_beneficiario = :usuario_id AND s.excluido = 0
    ORDER BY s.data_solicitacao ASC
");
$stmt->execute(['usuario_id' => $usuario_id]);
$solicitacoes = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

include '../templates/header.php';
?>

<h1>Minhas Solicitações</h1>

<?php if (empty($solicitacoes)): ?>
    <p>Você ainda não realizou nenhuma solicitação de tintas.</p>
<?php else: ?>
    <div class="solicitacoes-list">
        <!-- Botão para exibir/recolher todos os menus -->
        <button id="toggleDropdowns" data-expanded="false" onclick="toggleAllDropdowns()">Exibir Todos</button>
        <?php foreach ($solicitacoes as $id_solicitacao => $tintas): ?>
            <?php
            $solicitacao_info = $tintas[0];
            ?>
            <div class="solicitacao">
                <button class="solicitacao-toggle" onclick="toggleSolicitacao(<?php echo $id_solicitacao; ?>)">
                    Solicitação realizada em <?php echo date('d/m/Y', strtotime($solicitacao_info['data_solicitacao'])); ?>
                </button>
                <div id="solicitacao-<?php echo $id_solicitacao; ?>" class="solicitacao-details" style="display: none;">
                    <p><strong>Justificativa:</strong> <?php echo htmlspecialchars($solicitacao_info['justificativa']); ?></p>
                    <p><strong>Status da Solicitação:</strong> <?php echo htmlspecialchars($solicitacao_info['status_solicitacao']); ?></p>
                    
                    <?php if ($solicitacao_info['status_solicitacao'] == 'Aprovado'): ?>
                        <a href="retirada_tinta.php?id_solicitacao=<?php echo $id_solicitacao; ?>" class="btn-retirada">Verificar Disponibilidade de Retirada</a>
                    <?php endif; ?>

                    <table border="1">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Marca</th>
                                <th>Linha</th>
                                <th>Acabamento</th>
                                <th>Quantidade Solicitada (L)</th>
                                <th>Validade</th>
                                <th>Cor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tintas as $tinta): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($tinta['nome_tintas']); ?></td>
                                    <td><?php echo htmlspecialchars($tinta['marca']); ?></td>
                                    <td><?php echo htmlspecialchars($tinta['linha']); ?></td>
                                    <td><?php echo htmlspecialchars($tinta['acabamento']); ?></td>
                                    <td><?php echo number_format($tinta['quantidade'], 2, ',', '.'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($tinta['data_validade_tintas'])); ?></td>
                                    <td>
                                        <canvas class="color-preview" style="background-color: <?php echo htmlspecialchars($tinta['codigo_RGB']); ?>;"></canvas>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<a href="../index.php" class="btn-return">Voltar</a>
<script>
    function toggleSolicitacao(id) {
        const details = document.getElementById('solicitacao-' + id);
        if (details.style.display === 'none') {
            details.style.display = 'block';
        } else {
            details.style.display = 'none';
        }
    }
</script>

<?php include '../templates/footer.php'; ?>
