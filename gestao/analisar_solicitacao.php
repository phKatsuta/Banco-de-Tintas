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
    GROUP_CONCAT(CONCAT(T.id_tintas, ' (', ST.quantidade, ')') SEPARATOR ', ') AS tintas_solicitadas,
    T.id_tintas,
    T.quantidade_tintas_disponivel,
    T.excluido,
    A.id_gestor,
    A.status_solicitacao,
    A.justificativa,
    A.data_analise
FROM 
    Solicitacao S
INNER JOIN Solicitacao_tintas ST ON S.id_solicitacao = ST.id_solicitacao
INNER JOIN Tintas T ON ST.id_tintas = T.id_tintas
INNER JOIN Analise A ON S.id_solicitacao = A.id_solicitacao
INNER JOIN usuarios U ON S.id_beneficiario = U.id
WHERE A.status_solicitacao = 'Em analise'
GROUP BY S.id_solicitacao;
");

// Preparar e executar a consulta
$stmt_pendentes->execute();
$solicitacoes_pendentes = $stmt_pendentes->fetchAll(PDO::FETCH_ASSOC);

// Agrupar as solicitações por ID de solicitação
$solicitacoes = [];
foreach ($solicitacoes_pendentes as $solicitacao) {
    $id_solicitacao = $solicitacao['id_solicitacao'];
    $solicitacoes[$id_solicitacao][] = $solicitacao;
}

include '../templates/header.php';
?>

<h1>Analisar Solicitações</h1>

<details class="solicitacao-dropdown">
<summary><strong>PENDENTES</strong></summary>
    <form method="POST" action="analisar_solicitacao_submit.php">
        <?php if (empty($solicitacoes)): ?>
            <p>Não existem solicitações a serem analisadas.</p>
        <?php else: ?>
            <div class="solicitacao-list">
                <?php foreach ($solicitacoes as $id_solicitacao => $solicitacoes): ?>
                    <?php $solicitacao_info = $solicitacoes[0]; // Usamos o primeiro item de cada grupo ?>

                    <!-- Dropdown para cada solicitação -->
                    <details class="solicitacao-dropdown">
                        <summary><strong>Solicitação <?php echo htmlspecialchars($solicitacao_info['id_solicitacao']); ?></strong></summary>

                        <!-- Linha 1: Informações do Beneficiário -->
                        <div>
                            <strong>Usuário: </strong><br>
                            Código: <?php echo htmlspecialchars($solicitacao_info['id_beneficiario']); ?> <br>
                            Nome: <?php echo htmlspecialchars($solicitacao_info['usuario_nome']); ?><br>
                        </div>

                        <!-- Linha 2: Informações da Solicitação -->
                        <div>
                            <strong>Solicitação:</strong><br>
                            Código: <?php echo htmlspecialchars($solicitacao_info['id_solicitacao']); ?> <br>
                            Data: <?php echo date('d/m/Y', strtotime($solicitacao_info['data_solicitacao'])); ?> <br>
                            Justificativa: <?php echo htmlspecialchars($solicitacao_info['justificativa_solicitacao']); ?><br>
                        </div>

                        <!-- Linha 3: Tintas Solicitadas -->
                        <div>
                            <strong>Tintas Solicitadas:</strong>
                            <ul>
                                <?php foreach ($solicitacoes as $solicitacao): ?>
                                    <li>
                                        Tinta ID: <?php echo $solicitacao['id_tintas']; ?> - <br>
                                        Quantidade disponível:
                                        <?php echo number_format($solicitacao['quantidade_tintas_disponivel'], 2, ',', '.'); ?> L
                                        <?php if ($solicitacao['excluido'] == 1): ?>
                                            <span style="color: red;">(Tinta acabou)</span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <!-- Linha 4: Análise do Gestor -->
                        <div>
                            <strong>Análise:</strong>
                            <?php if ($solicitacao_info['status_solicitacao'] != 'Em analise'): ?>
                                <!-- Se a solicitação já foi analisada, exibe o ID do gestor e a justificativa -->
                                Gestor: <?php echo htmlspecialchars($solicitacao_info['id_gestor']); ?> -
                                Status: <?php echo htmlspecialchars($solicitacao_info['status_solicitacao']); ?> -
                                Justificativa: <?php echo htmlspecialchars($solicitacao_info['justificativa']); ?> -
                                Data da Análise: <?php echo date('d/m/Y H:i', strtotime($solicitacao_info['data_analise'])); ?>
                            <?php else: ?>
                                <!-- Se ainda está em análise, permite ao gestor alterar o status -->
                                <form action="processar_analise.php" method="POST">
                                    <input type="hidden" name="id_solicitacao"
                                        value="<?php echo $solicitacao_info['id_solicitacao']; ?>">
                                    <select name="status_analise">
                                        <option selected disabled>Em analise</option>
                                        <option value="Aprovada">Aprovado</option>
                                        <option value="Negada">Negado</option>
                                        <option value="Parcialmente aprovado">Parcialmente aprovado</option>
                                    </select>
                                    <textarea name="justificativa_analise" placeholder="Justifique a análise..."
                                        required></textarea>
                                    <button type="submit">Salvar Análise</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </details>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </form>
</details>
<a href="../index.php" class="btn-return">Voltar</a>

<?php include '../templates/footer.php'; ?>
<?php if (isset($_GET['success'])): ?>
    <p style="color: green;">Solicitação analisada com sucesso!</p>
<?php elseif (isset($_GET['error'])): ?>
    <p style="color: red;">Erro ao processar a solicitação. Tente novamente.</p>
<?php endif; ?>