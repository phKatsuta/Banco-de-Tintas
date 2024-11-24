<?php
require_once '../includes/config.php'; // Conexão com o banco de dados
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Dados do usuário logado
$usuario_id = $_SESSION['usuario_id'];
// Consultar os tipos do usuário
$sql = "SELECT tipo FROM Usuario_Tipos WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$tipos_usuario = [];
while ($row = $result->fetch_assoc()) {
    $tipos_usuario[] = $row['tipo'];
}

if (in_array('Gestor', $tipos_usuario)) {
    echo "O usuário é um Gestor";
} else if (in_array('Monitor', $tipos_usuario)) {
    echo "O usuário é um Monitor";
} else if (in_array('Doador', $tipos_usuario)) {
    echo "O usuário é um Doador";
} else if (in_array('Beneficiario', $tipos_usuario)) {
    echo "O usuário é um Beneficiário";
} else {
    echo "O usuário não possui nenhum tipo definido";
}
function gerarMenu($tipos_usuario) {
    // ... (código para gerar o menu HTML com base nos tipos)
    // Por exemplo, usando um switch case para cada tipo de usuário
    switch (true) {
        case in_array('Gestor', $tipos_usuario):
            // Mostrar menu de gestor
            break;
        case in_array('Monitor', $tipos_usuario):
            // Mostrar menu de monitor
            break;
        // ... outros casos
        default:
            // Mostrar menu padrão
    }
}

// Chamar a função para gerar o menu
gerarMenu($tipos_usuario);

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $local_doado = trim($_POST['local_doado']);
    $id_doador = ($usuario_tipo === 'Doador') ? $usuario_id : intval($_POST['id_doador']);
    $tintas = $_POST['tintas'] ?? [];

    // Validar campos obrigatórios
    $errors = [];
    if (empty($local_doado)) {
        $errors[] = "O local de doação é obrigatório.";
    }
    if (empty($tintas)) {
        $errors[] = "Você deve adicionar ao menos uma tinta.";
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Inserir a doação
            $stmt = $pdo->prepare("INSERT INTO Doacao (id_doador, id_monitor, data_doacao, local_doado) 
                                   VALUES (?, ?, CURRENT_TIMESTAMP, ?)");
            $id_monitor = ($usuario_tipo === 'Gestor' || $usuario_tipo === 'Monitor') ? $usuario_id : null;
            $stmt->execute([$id_doador, $id_monitor, $local_doado]);
            $id_doacao = $pdo->lastInsertId();

            // Inserir as tintas e associá-las à doação
            foreach ($tintas as $tinta) {
                $stmt_tinta = $pdo->prepare("INSERT INTO Tintas (nome_tintas, marca, linha, acabamento, 
                                           quantidade_tintas_disponivel, data_validade_tintas) 
                                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_tinta->execute([
                    $tinta['nome_tintas'],
                    $tinta['marca'],
                    $tinta['linha'],
                    $tinta['acabamento'],
                    $tinta['quantidade'],
                    $tinta['data_validade']
                ]);
                $id_tinta = $pdo->lastInsertId();

                // Vincular a tinta à doação
                $stmt_doacao_tinta = $pdo->prepare("INSERT INTO Doacao_tintas (id_doacao, id_tintas, quantidade_tintas_doada) 
                                                    VALUES (?, ?, ?)");
                $stmt_doacao_tinta->execute([$id_doacao, $id_tinta, $tinta['quantidade']]);
            }

            $pdo->commit();
            echo "<script>alert('Doação cadastrada com sucesso!');</script>";
            echo "<script>setTimeout(() => { window.location.href = 'index.php'; }, 3000);</script>";
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Erro ao salvar a doação: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Doação</title>
    <script>
        function addTinta() {
            const container = document.getElementById('tintas-container');
            const index = container.children.length;
            const tintaTemplate = `
                <div class="tinta">
                    <h4>Tinta ${index + 1}</h4>
                    <label>Nome:</label>
                    <input type="text" name="tintas[${index}][nome_tintas]" required><br>
                    <label>Marca:</label>
                    <input type="text" name="tintas[${index}][marca]" required><br>
                    <label>Linha:</label>
                    <input type="text" name="tintas[${index}][linha]"><br>
                    <label>Acabamento:</label>
                    <input type="text" name="tintas[${index}][acabamento]"><br>
                    <label>Quantidade (L):</label>
                    <input type="number" step="0.01" name="tintas[${index}][quantidade]" required><br>
                    <label>Data de Validade:</label>
                    <input type="date" name="tintas[${index}][data_validade]" required><br>
                </div>
                <hr>
            `;
            container.insertAdjacentHTML('beforeend', tintaTemplate);
        }
    </script>
</head>

<body>
    <h1>Cadastro de Doação</h1>

    <?php if (!empty($errors)): ?>
        <div style="color: red;">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label for="local_doado">Local de Doação:</label>
        <input type="text" name="local_doado" id="local_doado" required><br>

        <?php if ($usuario_tipo === 'Gestor' || $usuario_tipo === 'Monitor'): ?>
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
</body>

</html>