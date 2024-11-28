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
    T.id_tintas,       -- Certifique-se de que o campo 'id_tintas' está correto
    T.nome_tintas,      -- Verifique se 'nome_tinta' é o nome correto da coluna
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
GROUP BY S.id_solicitacao, T.id_tintas
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
//var_dump($solicitacoes_pendentes);  // Verifique os dados retornados

// Consulta para obter as solicitações processadas
$stmt_processadas = $pdo->prepare("
SELECT
    S.id_solicitacao,
    U1.id AS id_beneficiario,
    U1.usuario_nome AS solicitante,
    S.data_solicitacao,
    GROUP_CONCAT(CONCAT(T.id_tintas, ' (', ST.quantidade, ')') SEPARATOR ', ') AS tintas_solicitadas,
    SUM(ST.quantidade) AS total_tintas_solicitadas, -- Soma a quantidade total de tintas por solicitação
    U2.usuario_nome AS gestor,
    A.status_solicitacao,
    A.justificativa,
    A.data_analise,
    S.excluido AS solicitacao_excluida
FROM
    Solicitacao S
INNER JOIN Solicitacao_tintas ST ON S.id_solicitacao = ST.id_solicitacao
INNER JOIN Tintas T ON ST.id_tintas = T.id_tintas
INNER JOIN Analise A ON S.id_solicitacao = A.id_solicitacao
INNER JOIN Usuarios U1 ON S.id_beneficiario = U1.id
INNER JOIN Monitor M ON A.id_gestor = M.id_monitor
INNER JOIN Usuarios U2 ON M.id_monitor = U2.id
WHERE
    A.status_solicitacao != 'Em analise'
");
// Preparar e executar a consulta
$stmt_processadas->execute();
$solicitacoes_processadas = $stmt_processadas->fetchAll(PDO::FETCH_ASSOC);

// Agrupar as solicitações por ID de solicitação
$processadas = [];
foreach ($solicitacoes_processadas as $processada) {
    $id_processada = $processada['id_solicitacao'];
    $processadas[$id_processada][] = $processada;
}

include '../templates/header.php';
?>

<h1>Analisar Solicitações</h1>
<!--- Solicitações Pendentes --->
<?php
// Suponha que $solicitacoes_pendentes seja o array com as solicitações recuperadas do banco de dados

foreach ($solicitacoes_pendentes as $solicitacao_info) {
    echo '<form action="analisar_solicitacao_submit.php" method="POST">';
    
    echo '<strong>Solicitação de ' . htmlspecialchars($solicitacao_info['usuario_nome']) . ':</strong><br>';
    echo 'Justificativa: ' . (isset($solicitacao_info['justificativa_solicitacao']) ? htmlspecialchars($solicitacao_info['justificativa_solicitacao']) : 'Não disponível') . '<br>';
    
    // Opções de devolutiva
    echo '<strong>Devolutiva do Gestor:</strong><br>';
    echo '<label for="status_solicitacao">Status:</label>';
    echo '<select name="status_solicitacao" required>';
    echo '<option value="Aprovado">Aprovado</option>';
    echo '<option value="Parcialmente aprovado">Parcialmente aprovado</option>';
    echo '<option value="Negado">Negado</option>';
    echo '</select><br>';
    
    echo '<label for="justificativa">Justificativa:</label>';
    echo '<textarea name="justificativa" required></textarea><br>';
    
    // Passando o ID da solicitação e o ID do Gestor para o backend
    echo '<input type="hidden" name="id_solicitacao" value="' . htmlspecialchars($solicitacao_info['id_solicitacao']) . '">';
    echo '<input type="hidden" name="id_gestor" value="' . htmlspecialchars($usuario_logado['id_usuario']) . '">'; // Assume que $usuario_logado contém o usuário logado
    
    echo '<button type="submit">Salvar Devolutiva</button>';
    echo '</form><hr>';
}
?>

<!--- Solicitações Processadas --->
<div>
    <details class="solicitacao-dropdown">
        <summary><strong>PROCESSADOS</strong></summary>
        <form method="POST" action="analisar_solicitacao_submit.php">
            <?php if (empty($processadas)): ?>
            <p>Não existem solicitações processadas.</p>
            <?php else: ?>
            <div class="solicitacao-list">
                <?php foreach ($processadas as $id_solicitacao => $processadas): ?>
                <?php $solicitacao_info = $processadas[0]; // Usamos o primeiro item de cada grupo ?>

                <!-- Dropdown para cada solicitação -->
                        <details class="solicitacao-dropdown">
                            <summary><strong>Processado
                                    <?php echo htmlspecialchars($solicitacao_info['id_solicitacao']); ?></strong></summary>

                            <!-- Linha 1: Informações do Beneficiário -->
                            <div>
                                <strong>Usuário: </strong><br>
                                Código: <?php echo htmlspecialchars($solicitacao_info['id_beneficiario']); ?> <br>
                                Nome: <?php echo htmlspecialchars($solicitacao_info['solicitante']); ?><br>
                                <!-- Alterado para 'solicitante' -->
                            </div>

                            <!-- Linha 2: Informações da Solicitação -->
                            <div>
                                <strong>Solicitação:</strong><br>
                                Código: <?php echo htmlspecialchars($solicitacao_info['id_solicitacao']); ?> <br>
                                Data: <?php echo date('d/m/Y', strtotime($solicitacao_info['data_solicitacao'])); ?> <br>
                                Justificativa: <?php echo htmlspecialchars($solicitacao_info['justificativa']); ?><br>
                                <!-- Alterado para 'justificativa' -->
                            </div>

                            <!-- Linha 4: Análise do Gestor -->
                            <div>
                                <strong>Análise:</strong><?php if ($solicitacao_info['status_solicitacao'] != 'Em analise'): ?><br>
                                    Gestor: <?php echo htmlspecialchars($solicitacao_info['gestor']); ?><br>
                                    <!-- Alterado para 'gestor' -->
                                    Status: <?php echo htmlspecialchars($solicitacao_info['status_solicitacao']); ?><br>
                                    Justificativa: <?php echo htmlspecialchars($solicitacao_info['justificativa']); ?><br>
                                    Data da Análise:
                                    <?php echo date('d/m/Y H:i', strtotime($solicitacao_info['data_analise'])); ?><br>
                                <?php else: ?>
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
</div>
</div>
<a href="../index.php" class="btn-return">Voltar</a>

<?php include '../templates/footer.php'; ?>
<?php if (isset($_GET['success'])): ?>
    <p style="color: green;">Solicitação analisada com sucesso!</p>
<?php elseif (isset($_GET['error'])): ?>
    <p style="color: red;">Erro ao processar a solicitação. Tente novamente.</p>
<?php endif; ?>