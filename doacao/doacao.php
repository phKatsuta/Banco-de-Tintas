<?php
require_once '../includes/gera_menu.php';
require_once 'processa_doacao.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Obter o ID do usuário logado
$usuario_id = $_SESSION['usuario_id'];

// Obter os tipos de usuário logado
$tipos_usuario = getUserTypes($pdo, $usuario_id);

// Processar formulário de doação
$result = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = processDonationForm($pdo, $_POST, $usuario_id, $tipos_usuario);
    // Redirecionar para index.php após sucesso
    if (isset($result['success']) && $result['success']) {
        header("Location: ../index.php?success=1");
        exit();
    }
}

include '../templates/header.php';
?>

<h1>Cadastro de Doação</h1>

<?php
if (isset($result['errors']) && !empty($result['errors'])) {
    echo '<div style="color: red;"><ul>';
    foreach ($result['errors'] as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
    }
    echo '</ul></div>';
} elseif (isset($result['success']) && $result['success']) {
    echo '<p style="color: green;">Doação cadastrada com sucesso!</p>';
}
?>

<form method="POST">
    <label for="local_doado">Local de Doação:</label>
    <select name="local_doado" id="local_doado">
        <option value="FATEC">FATEC - Av. União dos Ferroviários, 1760</option>
        <optgroup label="Posto de Coleta">
            <option value="loja1">Saci Tintas Loja 1</option>
            <option value="loja2">Saci Tintas Loja 2</option>
            <option value="loja3">Saci Tintas Loja 3</option>
        </optgroup>
    </select><br>

    <?php if (!in_array('Doador', $tipos_usuario)): ?>
        <label for="id_doador">Doador:</label>
        <select name="id_doador" id="id_doador" required>
            <option value="1">Doação Anônima</option>
            <?php
            $stmt = $pdo->query("SELECT id, usuario_nome FROM Usuarios WHERE excluido = 0");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$row['id']}'>{$row['usuario_nome']}</option>";
            }
            ?>
        </select><br>
    <?php endif; ?>

    <h3>Tintas</h3>
    <div id="tintas-container"></div>
    <button type="button" onclick="addTinta()">Adicionar Tinta</button><br><br>

    <button type="submit">Cadastrar Doação</button>
</form>

<script>
    function addTinta() {
        const container = document.getElementById('tintas-container');
        const index = container.children.length;
        const tintaTemplate = `
        <div class="tinta" id="tinta-${index}">
            <label>Nome:</label>
            <input type="text" name="tintas[${index}][nome_tintas]" required><br>
            <label>Marca:</label>
            <input type="text" name="tintas[${index}][marca]"><br> <!---opcional--->
            <label>Linha:</label>
            <input type="text" name="tintas[${index}][linha]"><br> <!---opcional--->
            <label>Acabamento:</label>
            <input type="text" name="tintas[${index}][acabamento]"><br> <!---opcional--->
            <label>Quantidade (aproximada em litros. 700ml = 0,7L):</label>
            <input type="number" step="0.01" name="tintas[${index}][quantidade]" required><br>
            <label>Data de Validade:</label>
            <input type="date" name="tintas[${index}][data_validade]" required><br>
            <button type="button" onclick="removeTinta(${index})">Remover Tinta</button>
        </div>
        <hr>
    `;
        container.insertAdjacentHTML('beforeend', tintaTemplate);
    }

    function removeTinta(index) {
        const tintaDiv = document.getElementById('tinta-' + index);
        tintaDiv.remove();
    }
</script>

<?php include '../templates/footer.php'; ?>
