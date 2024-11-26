<?php
require_once  '../includes/gera_menu.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Recupera o ID do usuário logado
$usuario_id = $_SESSION['usuario_id'];

// Consulta os dados do usuário no banco
$query = "SELECT usuario_nome, usuario_cep, usuario_endereco, usuario_endereco_num, usuario_endereco_complemento,
                 usuario_bairro, usuario_cidade, usuario_estado, usuario_email, telefone, usuario_documento
          FROM Usuarios WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$usuario_id]);

if ($stmt->rowCount() > 0) {
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    echo "Usuário não encontrado.";
    exit();
}

// Consulta para obter os tipos de usuário associados
$tipos_query = "SELECT tipo FROM Usuario_Tipos WHERE usuario_id = ?";
$tipos_stmt = $pdo->prepare($tipos_query);
$tipos_stmt->execute([$usuario_id]);
$tipos_usuario = $tipos_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>

<h2>Editar Perfil</h2>
<form action="salvar_perfil.php" method="POST" id="form">
    <!-- Dados do Usuário -->
    <label for="usuario_nome">Nome:</label>
    <input type="text" name="usuario_nome" id="usuario_nome" value="<?= htmlspecialchars($user_data['usuario_nome']) ?>" readonly required><br>

    <label for="cep">CEP:</label>
    <input type="text" name="usuario_cep" id="cep" value="<?= htmlspecialchars($user_data['usuario_cep']) ?>" readonly>

    <button type="button" id="buscarCep" hidden>Buscar CEP</button>
        <div id="loading-indicator" style="display: none;">Carregando...</div><br>

    <label for="usuario_endereco">Endereço:</label>
    <input type="text" name="usuario_endereco" id="usuario_endereco" value="<?= htmlspecialchars($user_data['usuario_endereco']) ?>" readonly><br>

    <label for="endereco_num">Número:</label>
    <input type="text" name="usuario_endereco_num" id="endereco_num" value="<?= htmlspecialchars($user_data['usuario_endereco_num']) ?>" readonly><br>

    <label for="endereco_complemento">Complemento:</label>
    <input type="text" name="usuario_endereco_complemento" id="endereco_complemento" value="<?= htmlspecialchars($user_data['usuario_endereco_complemento']) ?>" readonly><br>

    <label for="bairro">Bairro:</label>
    <input type="text" name="usuario_bairro" id="usuario_bairro" value="<?= htmlspecialchars($user_data['usuario_bairro']) ?>" readonly><br>

    <label for="cidade">Cidade:</label>
    <input type="text" name="usuario_cidade" id="usuario_cidade" value="<?= htmlspecialchars($user_data['usuario_cidade']) ?>" readonly><br>

    <label for="estado">Estado:</label>
    <input type="text" name="usuario_estado" id="usuario_estado" value="<?= htmlspecialchars($user_data['usuario_estado']) ?>" readonly><br>

    <label for="usuario_email">Email:</label>
    <input type="email" name="usuario_email" id="usuario_email" value="<?= htmlspecialchars($user_data['usuario_email']) ?>" readonly required><br>

    <label for="telefone">Telefone:</label>
    <input type="text" name="telefone" id="telefone" value="<?= htmlspecialchars($user_data['telefone']) ?>" readonly required><br>

    <label for="usuario_documento">Documento (CPF ou CNPJ):</label>
    <input type="text" name="usuario_documento" id="usuario_documento" value="<?= htmlspecialchars($user_data['usuario_documento']) ?>" readonly><br>

    <!-- Tipos de Usuário -->
    <label>Deseja:</label><br>
    <input type="checkbox" name="tipos[]" value="Doador" id="doador" <?= in_array('Doador', $tipos_usuario) ? 'checked' : '' ?> disabled>
    <label for="doador">Doar tintas</label><br>
    <input type="checkbox" name="tipos[]" value="Beneficiario" id="beneficiario" <?= in_array('Beneficiario', $tipos_usuario) ? 'checked' : '' ?> disabled>
    <label for="beneficiario">Receber tintas</label><br>

    <!-- Botões -->
    <button type="button" onclick="habilitarEdicao()">Editar</button>
    <button type="submit" id="salvarBtn" style="display:none;">Salvar Alterações</button>
</form>

<script type="text/javascript" src="../SCRIPT/script_cadastro.js"></script>
</body>

</html>

<?php
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'sucesso') {
        echo "<p>Alterações salvas com sucesso!</p>";
    } elseif ($_GET['msg'] == 'nenhuma_alteracao') {
        echo "<p>Nenhuma alteração foi feita.</p>";
    }
}
?>