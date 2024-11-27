<?php
require_once '../includes/gera_menu.php';
include '../includes/via_cep.php';
include '../includes/busca_cep.php';
include '../includes/function_buscarEnderecoViaCep.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit();
}

// Recupera o ID do usuário logado
$usuario_id = $_SESSION['usuario_id'];

// Recupera os dados do usuário
$query = "SELECT usuario_nome, usuario_cep, usuario_endereco, usuario_endereco_num, usuario_endereco_complemento,
                 usuario_bairro, usuario_cidade, usuario_estado, usuario_email, telefone, usuario_documento
          FROM Usuarios WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$usuario_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_data) {
    echo "Usuário não encontrado.";
    exit();
}

// Atualiza os dados do usuário se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Dados enviados do formulário
    $usuario_nome = $_POST['usuario_nome'];
    $usuario_cep = $_POST['usuario_cep'];
    $usuario_endereco = $_POST['usuario_endereco'];
    $usuario_endereco_num = $_POST['usuario_endereco_num'];
    $usuario_endereco_complemento = $_POST['usuario_endereco_complemento'];
    $usuario_bairro = $_POST['usuario_bairro'];
    $usuario_cidade = $_POST['usuario_cidade'];
    $usuario_estado = $_POST['usuario_estado'];
    $usuario_email = $_POST['usuario_email'];
    $telefone = $_POST['telefone'];
    $usuario_documento = $_POST['usuario_documento'];

    // Tipos de usuário selecionados
    $tipos_usuario = isset($_POST['tipos']) ? $_POST['tipos'] : [];

    // Verifica se ao menos um tipo de usuário foi selecionado
    if (empty($tipos_usuario)) {
        echo "<script>alert('Por favor, selecione pelo menos um tipo de usuário.');</script>";
        // Redirecionar para a mesma página com os dados preenchidos
        echo "<meta http-equiv='refresh' content='0;url={$_SERVER['PHP_SELF']}'>";
        exit;
    } else {
        // Atualiza os dados do usuário na tabela `Usuarios`
        $query = "UPDATE Usuarios SET 
                    usuario_nome = ?, usuario_cep = ?, usuario_endereco = ?, usuario_endereco_num = ?, 
                    usuario_endereco_complemento = ?, usuario_bairro = ?, usuario_cidade = ?, 
                    usuario_estado = ?, usuario_email = ?, telefone = ?, usuario_documento = ?
                  WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $usuario_nome,
            $usuario_cep,
            $usuario_endereco,
            $usuario_endereco_num,
            $usuario_endereco_complemento,
            $usuario_bairro,
            $usuario_cidade,
            $usuario_estado,
            $usuario_email,
            $telefone,
            $usuario_documento,
            $usuario_id
        ]);

        // Atualiza os tipos de usuário na tabela Usuario_Tipos
        try {
            $pdo->beginTransaction();

            // Remove os tipos antigos
            $query = "DELETE FROM Usuario_Tipos WHERE usuario_id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$usuario_id]);

            // Insere os novos tipos selecionados
            foreach ($tipos_usuario as $tipo) {
                $query = "INSERT INTO Usuario_Tipos (usuario_id, tipo) VALUES (?, ?)";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$usuario_id, $tipo]);
            }

            $pdo->commit();
            echo "<p>Alterações salvas com sucesso! Você será redirecionado em 3 segundos.</p>";
            echo "<meta http-equiv='refresh' content='3;url=../index.php'>";
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo "Erro ao atualizar os tipos de usuário: " . $e->getMessage();
        }
    }
}
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
    <form method="POST" action="" id="form">
        <label for="usuario_nome">Nome:</label>
        <input type="text" name="usuario_nome" id="usuario_nome"
            value="<?= htmlspecialchars($user_data['usuario_nome']) ?>" readonly><br>

        <label for="usuario_cep">CEP:</label>
        <input type="text" name="usuario_cep" id="cep" oninput="aplicarMascaraCEP(this)"
            value="<?= htmlspecialchars($user_data['usuario_cep']) ?>" readonly>

        <button type="button" id="buscarCep" hidden>Buscar CEP</button>
        <div id="loading-indicator" style="display: none;">Carregando...</div><br>

        <label for="usuario_endereco">Endereço:</label>
        <input type="text" name="usuario_endereco" id="usuario_endereco"
            value="<?= htmlspecialchars($user_data['usuario_endereco']) ?>" readonly><br>

        <label for="usuario_endereco_num">Número:</label>
        <input type="text" name="usuario_endereco_num" id="usuario_endereco_num"
            value="<?= htmlspecialchars($user_data['usuario_endereco_num']) ?>" readonly><br>

        <label for="usuario_endereco_complemento">Complemento:</label>
        <input type="text" name="usuario_endereco_complemento" id="usuario_endereco_complemento"
            value="<?= htmlspecialchars($user_data['usuario_endereco_complemento']) ?>" readonly><br>

        <label for="usuario_bairro">Bairro:</label>
        <input type="text" name="usuario_bairro" id="usuario_bairro"
            value="<?= htmlspecialchars($user_data['usuario_bairro']) ?>" readonly><br>

        <label for="usuario_cidade">Cidade:</label>
        <input type="text" name="usuario_cidade" id="usuario_cidade"
            value="<?= htmlspecialchars($user_data['usuario_cidade']) ?>" readonly><br>

        <label for="usuario_estado">Estado:</label>
        <input type="text" name="usuario_estado" id="usuario_estado"
            value="<?= htmlspecialchars($user_data['usuario_estado']) ?>" readonly><br>

        <label for="usuario_email">Email:</label>
        <input type="email" name="usuario_email" id="usuario_email"
            value="<?= htmlspecialchars($user_data['usuario_email']) ?>" readonly><br>

        <label for="telefone">Telefone:</label>
        <input type="text" name="telefone" id="telefone" value="<?= htmlspecialchars($user_data['telefone']) ?>"
            readonly><br>

        <label for="usuario_documento">Documento:</label>
        <input type="text" name="usuario_documento" id="usuario_documento" oninput="mascaraDocumento(this)"
            value="<?= htmlspecialchars($user_data['usuario_documento']) ?>" readonly><br>

        <!-- Tipos de Usuário -->
        <label>Deseja:</label><br>
        <input type="checkbox" name="tipos[]" value="Doador" id="doador" <?= isset($tipos_usuario) && in_array('Doador', $tipos_usuario) ? 'checked' : '' ?> disabled>
        <label for="doador">Doar tintas</label><br>
        <input type="checkbox" name="tipos[]" value="Beneficiario" id="beneficiario" <?= isset($tipos_usuario) && in_array('Beneficiario', $tipos_usuario) ? 'checked' : '' ?> disabled>
        <label for="beneficiario">Receber tintas</label><br>

        <!-- Botões -->
        <button type="button" onclick="habilitarEdicao()">Editar</button>
        <button type="submit" id="salvarBtn" style="display: none;">Salvar Alterações</button>
    </form>

    <a href="../index.php">Voltar para a página inicial</a>
    <script type="text/javascript" src="../SCRIPT/script_cadastro.js"></script>
</body>

</html>