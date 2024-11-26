<?php
require_once '../includes/gera_menu.php';
// Verificar se o usuário tem permissão de Gestor ou Monitor
if (
    !isset($_SESSION['usuario_id']) ||
    !in_array('Gestor', $_SESSION['user_types']) && !in_array('Monitor', $_SESSION['user_types'])
) {
    header("Location: ../login.php");
    exit();
}

// Buscar doações pendentes de confirmação
$stmt = $pdo->query("
    SELECT 
        d.id_doacao,
        d.data_doacao,
        d.local_doado,
        t.id_tintas,
        t.nome_tintas,
        t.marca,
        t.linha,
        t.acabamento,
        dt.quantidade_tintas_doada,
        t.codigo_RGB
    FROM Doacao AS d
    INNER JOIN Doacao_tintas AS dt ON d.id_doacao = dt.id_doacao
    INNER JOIN Tintas AS t ON dt.id_tintas = t.id_tintas
    WHERE t.codigo_RGB IS NULL
    ORDER BY d.data_doacao ASC
");
$doacoes = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

include '../templates/header.php';
?>

<h1>Confirmação de Recebimento</h1>
<a href="../index.php" class="btn-return">Voltar</a>

<?php if (empty($doacoes)): ?>
    <p>Não há doações pendentes de confirmação.</p>
<?php else: ?>

    <form method="POST" enctype="multipart/form-data" action="recebimento_submit.php">
        <!-- Campo oculto para id_monitor -->
        <input type="hidden" name="id_monitor" value="<?php echo $_SESSION['usuario_id']; ?>">

        <div class="doacoes-list">
            <?php foreach ($doacoes as $id_doacao => $tintas): ?>
                <?php $doacao_info = $tintas[0]; ?>
                <div class="doacao">
                    <button type="button" class="doacao-toggle" onclick="toggleDoacao(<?php echo $id_doacao; ?>)">
                        Doação em <?php echo date('d/m/Y', strtotime($doacao_info['data_doacao'])); ?> - Local:
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
                                    <th>Cor (RGB)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tintas as $tinta): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($tinta['nome_tintas']); ?></td>
                                        <td><?php echo htmlspecialchars($tinta['marca']); ?></td>
                                        <td><?php echo htmlspecialchars($tinta['linha']); ?></td>
                                        <td><?php echo htmlspecialchars($tinta['acabamento']); ?></td>
                                        <td><?php echo number_format($tinta['quantidade_tintas_doada'], 2, ',', '.'); ?></td>
                                        <td>
                                            <input type="color" name="cor_rgb[<?php echo $tinta['id_tintas']; ?>]"
                                                value="<?php echo $tinta['codigo_RGB'] ?: '#ffffff'; ?>" required
                                                onchange="showColor(this.value, <?php echo $tinta['id_tintas']; ?>)">
                                            <span id="color-preview-<?php echo $tinta['id_tintas']; ?>"
                                                style="display:block; width: 30px; height: 30px; background-color: <?php echo $tinta['codigo_RGB'] ?: '#ffffff'; ?>;"></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button type="submit" name="confirmar[<?php echo $id_doacao; ?>]" class="btn-confirm">
                            Confirmar Recebimento
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button id="toggleDropdowns" data-expanded="false" onclick="toggleAllDropdowns()">Exibir Todos</button>
    </form>

    <script>
        function showColor(color, id) {
            const preview = document.getElementById('color-preview-' + id);
            preview.style.backgroundColor = color;
        }

        function toggleDoacao(id) {
            const details = document.getElementById('doacao-' + id);
            details.style.display = details.style.display === 'none' ? 'block' : 'none';
        }

        function toggleAllDropdowns() {
            const button = document.getElementById('toggleDropdowns');
            const expanded = button.getAttribute('data-expanded') === 'true';
            const details = document.querySelectorAll('.doacao-details');

            details.forEach(detail => {
                detail.style.display = expanded ? 'none' : 'block';
            });

            button.textContent = expanded ? 'Exibir Todos' : 'Recolher Todos';
            button.setAttribute('data-expanded', !expanded);
        }
    </script>

<?php endif; ?>

<?php include '../templates/footer.php'; ?>
