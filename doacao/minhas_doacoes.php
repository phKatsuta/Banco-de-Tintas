<?php
require_once '../includes/gera_menu.php';
// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location:" . BASE_URL . "login.php");
    exit();
}

// Obter o ID do usuário logado
$usuario_id = $_SESSION['usuario_id'];

// Consultar doações realizadas pelo usuário
$stmt = $pdo->prepare("
    SELECT 
        d.id_doacao,
        d.data_doacao,
        d.local_doado,
        t.nome_tintas,
        t.marca,
        t.linha,
        t.acabamento,
        t.quantidade_tintas_disponivel,
        t.data_validade_tintas,
        t.codigo_RGB
    FROM Doacao AS d
    INNER JOIN Doacao_tintas AS dt ON d.id_doacao = dt.id_doacao
    INNER JOIN Tintas AS t ON dt.id_tintas = t.id_tintas
    WHERE d.id_doador = :usuario_id
    ORDER BY d.data_doacao ASC
");
$stmt->execute(['usuario_id' => $usuario_id]);
$doacoes = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

include '../templates/header.php';
?>

<h1>Minhas Doações</h1>

<?php if (empty($doacoes)): ?>
    <p>Você ainda não realizou nenhuma doação.</p>
<?php else: ?>
    <div class="doacoes-list">
        <!-- Botão para exibir/recolher todos os menus -->
        <button id="toggleDropdowns" data-expanded="false" onclick="toggleAllDropdowns()">Exibir Todos</button>
        <?php foreach ($doacoes as $id_doacao => $tintas): ?>
            <?php
            $doacao_info = $tintas[0];
            ?>
            <div class="doacao">
                <button class="doacao-toggle" onclick="toggleDoacao(<?php echo $id_doacao; ?>)">
                    Doação realizada em <?php echo date('d/m/Y', strtotime($doacao_info['data_doacao'])); ?> no local:
                    <?php echo htmlspecialchars($doacao_info['local_doado']); ?>
                </button>
                <div id="doacao-<?php echo $id_doacao; ?>" class="doacao-details" style="display: none;">
                    <table border="1">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Marca</th>
                                <th>Linha</th>
                                <th>Acabamento</th>
                                <th>Quantidade (L)</th>
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
                                    <td><?php echo number_format($tinta['quantidade_tintas_disponivel'], 2, ',', '.'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($tinta['data_validade_tintas'])); ?></td>
                                    <td>
                                        <?php if ($tinta['codigo_RGB']): ?>
                                            <canvas class="color-preview"
                                                style="background-color: <?php echo htmlspecialchars($tinta['codigo_RGB']); ?>;"></canvas>
                                        <?php else: ?>
                                            <span style="color: red;">Doação a ser confirmada</span>
                                        <?php endif; ?>
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
    function toggleDoacao(id) {
        const details = document.getElementById('doacao-' + id);
        if (details.style.display === 'none') {
            details.style.display = 'block';
        } else {
            details.style.display = 'none';
        }
    }
</script>

<?php include '../templates/footer.php'; ?>