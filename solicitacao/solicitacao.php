<?php
require_once '../includes/config.php'; // Certifique-se de que isso inicializa $pdo
require_once '../includes/gera_menu.php';
require_once '../includes/verifica_usuario_tipo.php';
session_start();

// Verificar se o usuário está logado e se é do tipo 'Beneficiario'
if (!isset($_SESSION['usuario_id']) || !in_array('Beneficiario', getUserTypes($pdo, $_SESSION['usuario_id']))) {
    header("Location: login.php");
    exit();
}

// Obter o ID do usuário logado
$usuario_id = $_SESSION['usuario_id'];

// Obter as tintas disponíveis para solicitação
$stmt = $pdo->prepare("SELECT * FROM Tintas WHERE excluido = 0 AND quantidade_tintas_disponivel > 0 AND (codigo_RGB IS NOT NULL AND codigo_RGB != '')");
$stmt->execute();
$tintas = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../templates/header.php';
?>

<h1>Solicitação de Tintas</h1>

<form method="POST" action="processa_solicitacao.php">
    <h3>Selecione as Tintas que Deseja Solicitar</h3>

    <?php if (empty($tintas)): ?>
        <p style="color: red;">Não há tintas disponíveis para solicitação no momento.</p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; height: 90vh;">
            <div id="tintas-container"
                style="flex: 1; overflow-y: auto; border: 1px solid #ccc; padding: 10px; margin-bottom: 20px;">
                <?php foreach ($tintas as $index => $tinta): ?>
                    <div class="tinta" id="tinta-<?php echo $index; ?>"
                        style="display: flex; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
                        <div style="flex: 1; margin-right: 10px;">
                            <p><strong><?php echo htmlspecialchars($tinta['nome_tintas']); ?></strong></p>
                            <p>Marca: <?php echo htmlspecialchars($tinta['marca']); ?></p>
                            <p>Linha: <?php echo htmlspecialchars($tinta['linha']); ?></p>
                            <p>Acabamento: <?php echo htmlspecialchars($tinta['acabamento']); ?></p>
                            <p>Disponível: <?php echo number_format($tinta['quantidade_tintas_disponivel'], 2); ?> Litros</p>
                            <p>Validade: <?php echo htmlspecialchars($tinta['data_validade_tintas']); ?></p>
                            <?php if ($tinta['mistura']): ?>
                                <p><strong>Tinta combinada!</strong></p>
                            <?php endif; ?>
                            <input type="checkbox" name="tintas[<?php echo $tinta['id_tintas']; ?>][selecionada]"
                                id="selecionada-<?php echo $index; ?>" value="1"
                                onchange="toggleQuantidade(<?php echo $index; ?>)">
                            <label for="selecionada-<?php echo $index; ?>">Adicionar à Solicitação</label>
                            <div id="quantidade-container-<?php echo $index; ?>" style="display: none; margin-top: 10px;">
                                <label for="quantidade-<?php echo $index; ?>">Quantidade (Litros):</label>
                                <input type="number" step="0.01" name="tintas[<?php echo $tinta['id_tintas']; ?>][quantidade]"
                                    id="quantidade-<?php echo $index; ?>">
                            </div>
                        </div>
                        <div style="flex-shrink: 0;">
                            <p
                                style="background-color: <?php echo htmlspecialchars($tinta['codigo_RGB']); ?>; width: 50px; height: 50px;">
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Justificativa e Botão -->
            <div style="flex-shrink: 0;">
                <label for="justificativa">Justificativa:</label>
                <textarea name="justificativa" id="justificativa" rows="4" style="width: 100%;" required></textarea>
                <button type="submit" style="margin-top: 10px;">Enviar Solicitação</button>
            </div>
        </div>
    <?php endif; ?>
</form>
<script>
    function toggleQuantidade(index) {
        const checkbox = document.getElementById(`selecionada-${index}`);
        const quantidadeContainer = document.getElementById(`quantidade-container-${index}`);
        quantidadeContainer.style.display = checkbox.checked ? 'block' : 'none';
    }
</script>

<?php include '../templates/footer.php'; ?>