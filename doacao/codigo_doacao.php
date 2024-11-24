<?php
session_start();
require_once 'includes/config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'], $_SESSION['usuario_tipo'])) {
    die("Acesso não autorizado.");
}

$usuario_id = $_SESSION['usuario_id'];
$usuario_tipo = $_SESSION['usuario_tipo']; // Tipos possíveis: 'Doador', 'Gestor', 'Monitor'

// ID do usuário anônimo (já cadastrado no banco de dados)
$id_anonimo = 1;

// Inicializa variáveis de erro e sucesso
$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Dados do formulário
    $local_doado = trim($_POST['local_doado']);
    $id_doador = $usuario_tipo === 'Doador' ? $usuario_id : intval($_POST['id_doador'] ?? $id_anonimo); // Se não selecionado, assume anônimo
    $tintas = $_POST['tintas'] ?? [];

    if (empty($local_doado)) {
        $errors[] = "O local de doação deve ser informado.";
    }
    if (empty($tintas)) {
        $errors[] = "Selecione pelo menos uma tinta e a quantidade.";
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insere a doação
            $stmt_doacao = $pdo->prepare(
                "INSERT INTO Doacao (id_doador, data_doacao, local_doado) VALUES (?, NOW(), ?)"
            );
            $stmt_doacao->execute([$id_doador, $local_doado]);

            $id_doacao = $pdo->lastInsertId();

            // Insere as tintas da doação
            $stmt_tinta = $pdo->prepare(
                "INSERT INTO Doacao_tintas (id_doacao, id_tintas, quantidade_tintas_doada) VALUES (?, ?, ?)"
            );
            foreach ($tintas as $tinta) {
                $stmt_tinta->execute([$id_doacao, $tinta['id_tinta'], $tinta['quantidade']]);
            }

            $pdo->commit();
            $success = "Doação cadastrada com sucesso!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Erro ao salvar a doação: " . $e->getMessage();
        }
    }
}
?>


<?php
require_once 'includes/config.php'; // Conexão com o banco de dados

// Inicializa variáveis de erro/sucesso
$errors = [];
$success = "";

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Captura dados da doação
    $id_doador = $_POST["id_doador"];
    $local_doado = trim($_POST["local_doado"]);

    // Captura dados da tinta
    $nome_tintas = trim($_POST["nome_tintas"]);
    $marca = trim($_POST["marca"]);
    $linha = trim($_POST["linha"]);
    $acabamento = trim($_POST["acabamento"]);
    $quantidade_tintas_disponivel = $_POST["quantidade_tintas_disponivel"];
    $data_validade_tintas = $_POST["data_validade_tintas"];

    // Validações básicas
    if (empty($nome_tintas) || empty($local_doado) || empty($quantidade_tintas_disponivel) || empty($data_validade_tintas)) {
        $errors[] = "Todos os campos obrigatórios devem ser preenchidos.";
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Inserir tinta
            $sql_tinta = "INSERT INTO Tintas (
                nome_tintas, marca, linha, acabamento, quantidade_tintas_disponivel, data_validade_tintas
            ) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql_tinta);
            $stmt->execute([$nome_tintas, $marca, $linha, $acabamento, $quantidade_tintas_disponivel, $data_validade_tintas]);
            $id_tintas = $pdo->lastInsertId();

            // Inserir doação
            $sql_doacao = "INSERT INTO Doacao (id_doador, local_doado) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql_doacao);
            $stmt->execute([$id_doador, $local_doado]);
            $id_doacao = $pdo->lastInsertId();

            // Relacionar tinta à doação
            $sql_doacao_tinta = "INSERT INTO Doacao_tintas (id_doacao, id_tintas, quantidade_tintas_doada) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql_doacao_tinta);
            $stmt->execute([$id_doacao, $id_tintas, $quantidade_tintas_disponivel]);

            $pdo->commit();
            $success = "Doação cadastrada com sucesso!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Erro ao salvar os dados: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Doações</title>
</head>

<body>
    <h1>Cadastro de Doações</h1>
    <?php if (!empty($errors)): ?>
        <div style="color: red;">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php elseif (!empty($success)): ?>
        <div style="color: green;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <!-- Dados da doação -->
        <label for="id_doador">ID do Doador:</label>
        <input type="number" name="id_doador" id="id_doador" required><br>

        <label for="local_doado">Local de Entrega:</label>
        <select name="local_doado" id="local_doado">
            <option value="FATEC">FATEC</option>
            <optgroup label="Posto de Coleta">
                <option value="loja1">Saci Tintas Loja 1</option>
                <option value="loja2">Saci Tintas Loja 2</option>
                <option value="loja2">Saci Tintas Loja 3</option>
            </optgroup>
            <option value="Posto de Coleta">Posto de Coleta</option>
        </select><br>

        <!-- Dados da tinta -->
        <label for="nome_tintas">Nome da Tinta:</label>
        <input type="text" name="nome_tintas" id="nome_tintas" required><br>

        <label for="marca">Marca:</label>
        <input type="text" name="marca" id="marca"><br>

        <label for="linha">Linha:</label>
        <input type="text" name="linha" id="linha"><br>

        <label for="acabamento">Acabamento:</label>
        <input type="text" name="acabamento" id="acabamento"><br>

        <label for="quantidade_tintas_disponivel">Quantidade (L):</label>
        <input type="number" step="0.01" name="quantidade_tintas_disponivel" id="quantidade_tintas_disponivel"
            required><br>

        <label for="data_validade_tintas">Data de Validade:</label>
        <input type="date" name="data_validade_tintas" id="data_validade_tintas" required><br>

        <button type="submit">Cadastrar Doação</button>
    </form>

    <script type="text/javascript" src="SCRIPT\script_doacao.js"></script>

</body>

</html>

<form method="POST" action="cadastrar_doacao.php">
    <!-- Local da Doação -->
    <label for="local_doado">Local de Doação:</label>
    <select name="local_doado" id="local_doado" required>
        <option value="FATEC">FATEC</option>
        <option value="Posto de Coleta">Posto de Coleta</option>
    </select><br>

    <!-- Seleção do Doador (visível apenas para Gestor/Monitor) -->
    <?php if ($usuario_tipo === 'Gestor' || $usuario_tipo === 'Monitor'): ?>
        <label for="id_doador">Doador:</label>
        <select name="id_doador" id="id_doador">
            <option value="<?= $id_anonimo ?>">Doação Anônima</option>
            <?php
            $stmt_doador = $pdo->query("SELECT id, usuario_nome FROM Usuarios WHERE eh_empresa = 0");
            while ($doador = $stmt_doador->fetch()) {
                echo "<option value='{$doador['id']}'>{$doador['usuario_nome']}</option>";
            }
            ?>
        </select><br>
    <?php endif; ?>

    <!-- Seleção de Tintas -->
    <div id="tintas-container">
        <label>Tintas:</label>
        <?php
        $stmt_tintas = $pdo->query("SELECT id_tintas, nome_tintas FROM Tintas WHERE excluido = 0");
        while ($tinta = $stmt_tintas->fetch()) {
            ?>
            <div>
                <input type="checkbox" name="tintas[<?= $tinta['id_tintas'] ?>][id_tinta]" value="<?= $tinta['id_tintas'] ?>">
                <label><?= $tinta['nome_tintas'] ?></label>
                <input type="number" name="tintas[<?= $tinta['id_tintas'] ?>][quantidade]" step="0.01" min="0" placeholder="Quantidade (L)">
            </div>
            <?php
        }
        ?>
    </div>

    <!-- Botão de envio -->
    <button type="submit">Cadastrar Doação</button>
</form>

